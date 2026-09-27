# Hydrator (ou Data Hydration)

> Os DTOs e os Hydrators trabalham juntos: o DTO é a estrutura que segura os dados, e o Hydrator é o mecanismo que pega um array (seja do formulário HTTP ou do banco) e injeta de forma dinâmica nas propriedades de um objeto (que pode ser um DTO ou uma Entidade de Domínio).

## O padrão Hydrator é amplamente utilizado em todo o ecossistema PHP. Os três cenários principais de uso são:

### 1. Camada de Persistência (ORMs e Banco de Dados)
Este é o uso mais famoso do padrão no PHP. Frameworks e ORMs como o Doctrine utilizam Hydrators intensamente. 
* Como funciona: Quando você faz um SELECT no banco de dados, o driver (PDO) te devolve um array plano de strings e números. O ORM utiliza um Hydrator para transformar essa tabela de banco de dados em instâncias complexas das suas classes de Domínio (Entidades), preenchendo inclusive propriedades privadas sem precisar usar métodos set.

### 2. Frameworks Web (Mapeamento de Formulários e APIs)
> Muitos frameworks utilizam componentes de hidratação para acoplar dados da requisição diretamente à objetos da aplicação.
* Zend / Laminas Framework: Historicamente possui o componente Laminas\Hydrator, feito exatamente para mover dados de forma bidirecional entre arrays de formulários HTML e objetos de modelo. 
* Symfony Form Component: Por trás dos panos, os "Data Mappers" do Symfony agem como hydrators, pegando o POST enviado e populando seu objeto de dados.

### 3. Serialização de APIs (Mapeamento Bidirecional)
Bibliotecas modernas de manipulação de dados (como CuyZ/Valnor ou Serializer do Symfony) utilizam a hidratação e a extração para:
* Hydration (Hidratação): Pegar um JSON recebido por uma API externa e transformá-lo em um objeto PHP tipado.
* Extraction (Extração): Fazer o inverso, ler um objeto PHP e extrair um array plano para ser convertido em JSON ou salvo em sessões e caches.

------------------------------
## Resumo da ópera: Quem é quem?

| Componente | Função | Exemplo em PHP |
|---|---|---|
| DTO | O objeto de transporte (uma classe readonly pura e anêmica). | class UserDTO { public string $name; } |
| Hydrator | O mecanismo (robô) que usa reflexão para ler o formulário/banco e construir o DTO/Entidade. | class UserHydrator { ... } |

Se você estiver construindo esse robô do zero na mão usando Reflection, quer que eu te mostre um exemplo de implementação simples de um Hydrator genérico em PHP para entender melhor a separação dele com o DTO?

# 
Um Serviço Utilitário de Infraestrutura.
Considerações técnicas rápidas e melhorias pontuais para torná-lo ainda mais robusto no PHP moderno, prevenindo alguns comportamentos inesperados do ecossistema:
## 1. Cuidados com a tipagem no PHP 8.1+ (Union Types)
No PHP moderno, os parâmetros podem ter múltiplos tipos (ex: int|string|null). O método $params[0]->getType()?->getName() vai falhar lançando um erro se o tipo for uma união, porque ele retornará um objeto ReflectionUnionType, que não possui o método getName().

## 2. Parâmetros Intermitentes e Nulos  
* Se o formulário HTML enviar um campo em branco e sua entidade aceitar null, o trim((string)$request[$fieldName]) !== '' vai ignorar o campo em vez de anular a propriedade. Se a intenção for resetar o valor da entidade, vale a pena permitir o null.
------------------------------
## Versão Refinada do seu HydractorRobot
Aqui está uma versão ajustada para blindar seu robô contra tipos complexos e herança de interfaces (como DateTimeInterface):
```php
getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $methodName = $method->getName();

            if (str_starts_with($methodName, 'set') && $method->getNumberOfParameters() === 1) {
                $propertyName = substr($methodName, 3);
                $fieldName = lcfirst($propertyName);
                
                // Modificado para aceitar o campo mesmo se ele for enviado vazio/null 
                // caso o formulário queira limpar o dado.
                if (array_key_exists($fieldName, $request)) {
                    $paramValue = $request[$fieldName];

                    try {
                        $params = $method->getParameters();
                        $type = $params[0]->getType();
                        
                        // Garante compatibilidade caso o tipo seja ReflectionNamedType
                        // Se for Union Type ou Intersection Type, lidamos como string/mixed padrão
                        $parameterType = $type instanceof ReflectionNamedType ? $type->getName() : null;
                        
                        $convertedValue = self::convertValue($paramValue, $parameterType);
                        
                        $method->invoke($entity, $convertedValue);
                    } catch (Exception $e) {
                        error_log("Erro ao popular campo $fieldName: " . $e->getMessage());
                    }
                }
            }
        }
        return $entity;
    }

    private static function convertValue(mixed $value, ?string $targetType): mixed
    {
        // Se o valor já for nulo ou vazio e aceitar string vazia, mantemos o fluxo natural
        if ($value === null || $value === '') {
            return match ($targetType) {
                'string' => '',
                default => null
            };
        }

        return match ($targetType) {
            'string' => (string)$value,
            'int', 'integer' => (int)$value,
            'float', 'double' => (float)$value,
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            // Tratamento robusto para qualquer variação de DateTime
            'DateTime', 'DateTimeInterface', DateTimeInterface::class => new DateTime((string)$value),
            'DateTimeImmutable', DateTimeImmutable::class => new DateTimeImmutable((string)$value),
            'BigDecimal' => (string)$value,
            default => $value,
        };
    }
}
```
## O que foi implementado e por quê?

* array_key_exists em vez de isset: Se o formulário HTML enviar um campo nulo ou vazio e sua entidade aceitar nulo, o isset ignoraria a mudança. O array_key_exists garante que se o campo veio no formulário, a aplicação tentará atualizar a entidade correspondente.
* Segurança de Reflexão (ReflectionNamedType): Evita quebras fatais de código se alguma Entidade sua utilizar propriedades unificadas (ex: public function setAge(int|string $age)).
* Uso do ::class para objetos: Usar DateTimeInterface::class protege a sua validação caso namespaces absolutos entrem em jogo na leitura do tipo.

