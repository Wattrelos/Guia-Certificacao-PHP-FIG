
# PHP-FIG e Padrões de Projeto

## 1. Padrões de Projeto cobrados na Certificação Zend (PHP Engineer)
A prova de certificação oficial da Zend exige conhecimento prático em Padrões de Projeto (Design Patterns) clássicos e arquiteturais aplicados ao ecossistema PHP:

* Padrões de Criação:
    - Factory / Factory Method: Centralização da lógica de criação de objetos.
    - Singleton: Garantir que uma classe tenha apenas uma única instância global. 
* Padrões Estruturais:
    - Adapter: Permitir que classes com interfaces incompatíveis trabalhem juntas.
    - Dependency Injection (Injeção de Dependência): Passar dependências externas para uma classe em vez de criá-las internamente.
    - Registry: Armazenar instâncias de objetos globalmente (comum no legado PHP). 
* Padrões Comportamentais:
    - Strategy: Definir uma família de algoritmos intercambiáveis.
    - Observer: Notificar múltiplos objetos sobre mudanças de estado de um sujeito. 
* Padrões Arquiteturais:
    - MVC (Model-View-Controller): Separação de conceitos em aplicações Web.
    - Front Controller: Um único ponto de entrada para todas as requisições da aplicação (centralizado geralmente no index.php). 

------------------------------
## 2. O que a PHP-FIG cobra (as PSRs mais importantes)
Se você deseja alinhar seu código com os padrões da PHP-FIG para entrevistas ou exames práticos, você deve dominar as principais especificações vigentes: 

* Estilo e Formatação de Código:
  - PSR-1 (Padrão Básico de Codificação): Regras de tags, nomes de métodos em camelCase, classes em StudlyCaps, etc.
  - PSR-12 (Guia de Estilo Estendido): Substituiu a antiga PSR-2, definindo o espaçamento exato e regras de chaves de controle. [17, 18, 19, 20] 
* Carregamento de Arquivos:
  - PSR-4 (Autoloading): O padrão oficial para mapear namespaces diretamente para caminhos de arquivos em disco. 
* Interfaces e Contratos Comuns:
  - PSR-3 (Logger Interface): Padronização para componentes de Log.
  - PSR-7 / PSR-15 / PSR-17 / PSR-18: O ecossistema HTTP (Mensagens HTTP, Middlewares, Factories e HTTP Clients).
  - PSR-11 (Container Interface): O padrão para containers de injeção de dependência.
  - PSR-16 (Simple Cache): Interface para manipulação simplificada de cache de dados. 



## Padrões de Projeto

### Injeção de Dependência (Dependency Injection - DI)
> DI é um padrão de projeto (design pattern) estrutural que serve para implementar o princípio da Inversão de Controle (IoC).

#### Como funciona a Injeção de Dependência?
Em vez de uma classe criar os objetos de que precisa para funcionar (como uma conexão com banco de dados ou um serviço de e-mail) por conta própria, esses objetos (dependências) são entregues (injetados) para ela de fora, geralmente pelo construtor ou por métodos.
#### Formas de Injeção

* Injeção via Construtor: As dependências são passadas quando o objeto é criado. É o formato mais recomendado e seguro.
* Injeção via Método (Setter): As dependências são passadas chamando um método específico depois que o objeto já foi instanciado.
* Injeção via Interface: A classe implementa uma interface que força a exigência de injetar a dependência.

#### Principais Benefícios

* Baixo Acoplamento: As classes ficam independentes e não precisam conhecer os detalhes de como criar suas dependências.
* Facilidade de Testes (Testabilidade): Fica simples substituir objetos reais por mocks ou stubs durante os testes automatizados.
* Manutenibilidade: Alterações em uma dependência afetam menos as classes que a utilizam.

### Exemplo prático




#### 🔍 O que foi abordado:

1. **Implementação Real de [MySQLConnection.php](/backend/src/Infrastructure/Persistence/Connection/MySQLConnection.php)**:
   - **Injeção via Construtor**: Pode receber uma instância pré-existente de `PDO` (ideal para mocks ou pools) ou os parâmetros de conexão.
   - **Integração com [backend/.env](/backend/.env)**: Foi adicionado suporte a carregamento nativo e um método de fábrica `MySQLConnection::createFromEnv()`, que lê as credenciais de `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD`.
   - **Prepared Statements**: Métodos `query()` e `execute()` com binding de parâmetros e tratamento robusto de erros.

2. **Tipagem e Contrato em [DatabaseConnectionInterface.php](/backend/src/Infrastructure/Persistence/Connection/DatabaseConnectionInterface.php)**:
   - Adicionada tipagem estrita (`declare(strict_types=1);`), suporte a parâmetros de prepared statement (`array $params = []`) e método `getPdo(): ?PDO`.

3. **Demonstração Prática de Intercambiabilidade e Testes**:
   - Criação de [PostgreSQLConnection.php](/backend/src/Infrastructure/Persistence/Connection/PostgreSQLConnection.php): Mostra como trocar o driver sem alterar a regra de negócio.
   - Criação de [InMemoryConnection.php](/backend/src/Infrastructure/Persistence/Connection/InMemoryConnection.php): Implementação mock para demonstrar a maior vantagem do DI em relação ao Singleton — a capacidade de rodar **testes unitários em milissegundos sem depender de banco de dados**.

4. **Script Executável [dependency_injection.php](/principais-certificacoes/PHP-FIG/Design-Patterns/dependency-injection/dependency_injection.php)**:
   - O arquivo estava vazio (0 bytes). Agora é um script de demonstração completo que executa 3 cenários no terminal:
     1. Conexão real de produção com MySQL lendo o `.env`.
     2. Migração imediata para PostgreSQL com zero linhas alteradas na classe [OrderService](/principais-certificacoes/PHP-FIG/Design-Patterns/dependency-injection/dependency_injection.php#L31).
     3. Teste unitário automatizado com validação de consulta em memória.

5. **Modelagem Visual em [dependency_injection.puml](/principais-certificacoes/PHP-FIG/Design-Patterns/dependency-injection/dependency_injection.puml)**:
   - Diagrama PlantUML ilustrando a relação entre a camada de aplicação e a camada de infraestrutura via Princípio de Inversão de Dependência (DIP / SOLID).

6. **Tutorial Didático Enriquecido em [dependency_injection.md](/principais-certificacoes/PHP-FIG/Design-Patterns/dependency-injection/dependency_injection.md)**:
   - Tabela comparativa direta: **Acoplamento com `new` vs. Singleton vs. Dependency Injection**.
   - Explicação de *Constructor Property Promotion* do PHP 8+.
   - Contextualização com os padrões do mercado: **PSR-11 (Container Interface)** e como containers de DI funcionam em frameworks como Laravel e Symfony.

7. **Correção do `.gitignore`**:
   - O arquivo de ignore na raiz continha um espaço no nome (`" .gitignore"`), o que impedia o Git de ignorar os arquivos `.env`. O nome foi normalizado para `.gitignore` e os arquivos sensíveis agora estão devidamente protegidos do versionamento.

---

#### 🧪 Executando a Demonstração

Para rodar o exemplo prático no terminal:

```bash
php principais-certificacoes/PHP-FIG/Design-Patterns/dependency-injection/dependency_injection.php
```

**Saída no console:**
```text
====================================================================
 DEMONSTRAÇÃO: PADRÃO INJEÇÃO DE DEPENDÊNCIA (DI) NO PHP MODERNO     
====================================================================

--- CENÁRIO 1: Produção com MySQL (Dados lidos de backend/.env) ---
• Host: 127.0.0.1:3306
• Database: guia_desenvolvimento_software
• Username: desenvolvedor
• Status PDO: Credenciais do .env carregadas com sucesso.
🛒 [OrderService]: Processando checkout do pedido #101...
⚡ [MySQLConnection - Log]: Executando consulta: UPDATE orders SET status = 'paid', updated_at = NOW() WHERE id = :id
✅ [OrderService]: Pedido #101 pago com sucesso!

--- CENÁRIO 2: Troca de Driver para PostgreSQL ---
Imagine que a empresa decidiu migrar do MySQL para o PostgreSQL.
A classe OrderService NÃO precisa ser alterada em NENHUMA linha!

🛒 [OrderService]: Processando checkout do pedido #202...
🐘 [PostgreSQLConnection - Log]: Executando consulta: UPDATE orders SET status = 'paid', updated_at = NOW() WHERE id = :id
✅ [OrderService]: Pedido #202 pago com sucesso!

--- CENÁRIO 3: Teste Unitário Automatizado (Sem Banco de Dados) ---
Aqui vemos a maior vantagem sobre o Singleton: o desacoplamento permite
testar a regra de negócio sem precisar de servidores ou bancos reais ativos.

🛒 [OrderService]: Processando checkout do pedido #303...
🧪 [InMemoryConnection - Mock]: Query registrada para teste: UPDATE orders SET status = 'paid', updated_at = NOW() WHERE id = :id
✅ [OrderService]: Pedido #303 pago com sucesso!

🎉 [TESTE PASSOU]: A consulta esperada de pagamento foi disparada corretamente!
====================================================================
```


