# Singleton (Padrão Criacional)

> O padrão **Singleton** é utilizado para garantir que uma classe tenha apenas uma única instância ativa em toda a execução do sistema e fornecer um ponto de acesso global para ela. No PHP, ele é comumente empregado no gerenciamento de conexões com bancos de dados e carregamento de configurações globais.

Veja o código prático em [simpleton.php](file:///var/www/html/Guia_desenvolvimento_software/principais-certificacoes/PHP-FIG/Design-Patterns/singleton/simpleton.php) e o diagrama UML em [simpleton.puml](file:///var/www/html/Guia_desenvolvimento_software/principais-certificacoes/PHP-FIG/Design-Patterns/singleton/simpleton.puml).

---

## 🧱 Os 4 Pilares do Singleton no PHP Moderno (PHP 8+)

Para garantir que o padrão seja inviolável, é necessário fechar todas as portas de instanciação e clonagem do PHP:

1. **`private __construct()`**: Impede a criação de novas instâncias via operador `new` fora da classe.
2. **`private __clone()`**: Impede a duplicação do objeto através da palavra-chave `clone`.
3. **`public __wakeup()` / `__unserialize()`**: No PHP 8+, métodos de desserialização devem ser públicos e lançar uma `\Exception`, impedindo que o objeto seja recriado via `unserialize()`.
4. **`public static getInstance()`**: O único ponto de entrada para recuperar e reutilizar a instância compartilhada (armazena em propriedade estática privada).

---

## 🚀 Exemplo de Execução e Integração com `.env`

O exemplo prático em [simpleton.php](file:///var/www/html/Guia_desenvolvimento_software/principais-certificacoes/PHP-FIG/Design-Patterns/singleton/simpleton.php) carrega de forma autônoma as configurações de conexão definidas no arquivo [.env](file:///var/www/html/Guia_desenvolvimento_software/principais-certificacoes/PHP-FIG/Design-Patterns/.env):

```php
// Obtendo a primeira instância
$db1 = DatabaseConnection::getInstance();
echo "ID da Conexão 1: " . $db1->getConnectionId() . "\n";

// Obtendo a segunda instância
$db2 = DatabaseConnection::getInstance();
echo "ID da Conexão 2: " . $db2->getConnectionId() . "\n";

// Verificação lógica: ambas as variáveis apontam para o mesmo objeto na memória
if ($db1 === $db2) {
    echo "✅ Sucesso: Ambas as variáveis usam a mesma e única instância!\n";
}
```

---

## 💬 Por que o Singleton é considerado um "Anti-Padrão"?

Embora resolva a necessidade de instância única, o Singleton introduz alto acoplamento e estado global estático, tornando testes automatizados (unitários) difíceis de isolar e mockar.

👉 Veja [como o padrão Dependency Injection (Injeção de Dependência) substitui o Singleton no PHP moderno](file:///var/www/html/Guia_desenvolvimento_software/principais-certificacoes/PHP-FIG/Design-Patterns/dependency-injection/dependency_injection.md).
