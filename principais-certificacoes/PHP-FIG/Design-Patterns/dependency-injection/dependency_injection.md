# Injeção de Dependência (Dependency Injection - DI)

> A **Injeção de Dependência (DI)** é uma técnica de design e arquitetura de software onde um objeto recebe suas dependências de fontes externas, em vez de criá-las internamente ou obtê-las através de chamadas estáticas globais (como no padrão Singleton).
> 
> No ecossistema PHP moderno e alinhado aos padrões da **PHP-FIG (especialmente a PSR-11: Container Interface)**, a Injeção de Dependência é o pilar central de frameworks consolidados como **Laravel** e **Symfony**.

Veja o código executável em [dependency_injection.php](file:///var/www/html/Guia_desenvolvimento_software/principais-certificacoes/PHP-FIG/Design-Patterns/dependency-injection/dependency_injection.php) e a modelagem visual em [dependency_injection.puml](file:///var/www/html/Guia_desenvolvimento_software/principais-certificacoes/PHP-FIG/Design-Patterns/dependency-injection/dependency_injection.puml).

---

## 🧭 O Problema: Acoplamento Rígido vs Dependência Explícita

Observe as três abordagens clássicas para gerenciar dependências:

| Critério | ❌ Acoplamento com `new` | ⚠️ Singleton Global | ✅ Injeção de Dependência (DI) |
| :--- | :--- | :--- | :--- |
| **Origem da Dependência** | Criada dentro da própria classe | Obtida via `DatabaseConnection::getInstance()` | Injetada de fora (ex: via construtor) |
| **Acoplamento** | Altíssimo (preso à classe concreta) | Alto (dependência oculta e estática) | Mínimo (depende apenas de uma Interface) |
| **Testabilidade** | Impossível testar sem banco real | Complexa (estado global contamina testes) | Excepcional (injeta Mocks/In-Memory em segundos) |
| **Flexibilidade** | Nula (trocar banco exige alterar o serviço) | Baixa (uma única instância global) | Total (troca de tecnologia via configuração) |

---

## 1. O Jeito Ruim (Sem Injeção de Dependência)

Na abordagem incorreta, a classe de negócio cria a dependência por conta própria. Isso viola o **Princípio da Responsabilidade Única (SRP)** e o **Princípio da Inversão de Dependência (DIP)**:

```php
<?php

class BadOrderService 
{
    private MySQLConnection $db;

    public function __construct() 
    {
        // ❌ Problema: A classe está rigidamente presa ao MySQLConnection.
        // Não é possível rodar testes sem um servidor MySQL ativo na máquina!
        $this->db = new MySQLConnection(); 
    }

    public function checkout(int $orderId): void 
    {
        $this->db->query("UPDATE orders SET status = 'paid' WHERE id = :id", ['id' => $orderId]);
    }
}
```

---

## 2. O Jeito Certo: Injeção de Dependência com Interfaces

Para aplicar o padrão da forma mais sólida possível, desacoplamos a regra de negócio da infraestrutura através de um contrato (Interface).

```
   ┌──────────────────────┐
   │     OrderService     │ (Camada de Aplicação / Regra de Negócio)
   └──────────┬───────────┘
              │  usa (Injeção via Construtor)
              ▼
   ┌──────────────────────────────────┐
   │   DatabaseConnectionInterface    │ (Contrato Abstrato)
   └──────────┬───────────────────────┘
              ▲
              │ implementam
     ┌────────┴────────┬───────────────────┐
     │                 │                   │
┌────┴────────────┐ ┌──┴───────────────┐ ┌─┴───────────────────┐
│ MySQLConnection │ │ PostgreSQLConn.  │ │ InMemoryConnection  │
│ (Lê do .env)    │ │ (Outro Driver)   │ │ (Mock para Testes)  │
└─────────────────┘ └──────────────────┘ └─────────────────────┘
```

### Passo 1: Definir o Contrato (Interface)

A interface estabelece o contrato que qualquer conexão deve cumprir, sem se importar com o driver específico:

Arquivo: [DatabaseConnectionInterface.php](file:///var/www/html/Guia_desenvolvimento_software/backend/src/Infrastructure/Persistence/Connection/DatabaseConnectionInterface.php)

```php
<?php

declare(strict_types=1);

namespace Src\Infrastructure\Persistence\Connection;

use PDO;

interface DatabaseConnectionInterface 
{
    public function query(string $sql, array $params = []): array;
    public function execute(string $sql, array $params = []): int;
    public function getPdo(): ?PDO;
}
```

---

### Passo 2: Criar as Implementações Concretas

#### A. Conexão Real com MySQL conectada ao `.env`

A classe [MySQLConnection.php](file:///var/www/html/Guia_desenvolvimento_software/backend/src/Infrastructure/Persistence/Connection/MySQLConnection.php) implementa o contrato e permite injetar credenciais ou carregá-las de forma segura a partir do arquivo [.env](file:///var/www/html/Guia_desenvolvimento_software/backend/.env):

```php
<?php

namespace Src\Infrastructure\Persistence\Connection;

use PDO;

class MySQLConnection implements DatabaseConnectionInterface 
{
    private ?PDO $pdo = null;

    public function __construct(
        ?PDO $pdo = null,
        ?string $host = null,
        ?string $database = null,
        ?string $username = null,
        ?string $password = null,
        int $port = 3306
    ) {
        // Se parâmetros não forem passados, lê as variáveis do backend/.env
        $host     = $host ?? $_ENV['DB_HOST'] ?? '127.0.0.1';
        $database = $database ?? $_ENV['DB_DATABASE'] ?? 'guia_desenvolvimento_software';
        $username = $username ?? $_ENV['DB_USERNAME'] ?? 'desenvolvedor';
        $password = $password ?? $_ENV['DB_PASSWORD'] ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $username, $password);
    }

    public function query(string $sql, array $params = []): array 
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function execute(string $sql, array $params = []): int 
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function getPdo(): ?PDO 
    {
        return $this->pdo;
    }
}
```

#### B. Implementação Mock para Testes Unitários Rápidos

Criada em [InMemoryConnection.php](file:///var/www/html/Guia_desenvolvimento_software/backend/src/Infrastructure/Persistence/Connection/InMemoryConnection.php), ela não acessa a rede e armazena as instruções executadas em memória:

```php
<?php

namespace Src\Infrastructure\Persistence\Connection;

class InMemoryConnection implements DatabaseConnectionInterface 
{
    private array $executedQueries = [];

    public function query(string $sql, array $params = []): array 
    {
        $this->executedQueries[] = $sql;
        return [];
    }

    public function execute(string $sql, array $params = []): int 
    {
        $this->executedQueries[] = $sql;
        return 1;
    }

    public function getPdo(): ?\PDO { return null; }

    public function hasExecuted(string $fragment): bool 
    {
        return !empty(array_filter($this->executedQueries, fn($q) => str_contains($q, $fragment)));
    }
}
```

---

### Passo 3: A Classe de Negócio com Constructor Injection

A classe de negócio declara no construtor a **Interface** de que necessita. No PHP 8+, utilizamos a *Promoção de Propriedades no Construtor*:

```php
<?php

use Src\Infrastructure\Persistence\Connection\DatabaseConnectionInterface;

class OrderService 
{
    // ✅ Injeção de Dependência via Construtor:
    // A classe não sabe se é MySQL, Postgres ou Mock. Ela apenas usa o contrato!
    public function __construct(
        private DatabaseConnectionInterface $db
    ) {}

    public function checkout(int $orderId): bool 
    {
        echo "🛒 Processando pedido #{$orderId}...\n";
        
        $this->db->query(
            "UPDATE orders SET status = 'paid' WHERE id = :id", 
            ['id' => $orderId]
        );

        return true;
    }
}
```

---

### Passo 4: Executando nos Diferentes Cenários

O poder do padrão se torna evidente na hora de instanciar a aplicação:

#### Cenário 1: Produção com MySQL (Dados do `.env`)
```php
$mysql = MySQLConnection::createFromEnv(); // lê backend/.env
$orderService = new OrderService($mysql);
$orderService->checkout(101);
```

#### Cenário 2: Migração para PostgreSQL (Zero Alterações no `OrderService`)
```php
$postgres = new PostgreSQLConnection(host: '127.0.0.1', database: 'ecommerce', username: 'postgres');
$orderService = new OrderService($postgres);
$orderService->checkout(202);
```

#### Cenário 3: Teste Unitário Automatizado (Zero Banco de Dados)
```php
$mockDb = new InMemoryConnection();
$orderService = new OrderService($mockDb);
$orderService->checkout(303);

// Validação do teste:
assert($mockDb->hasExecuted("UPDATE orders SET status = 'paid'"));
echo "✅ Teste passou sem tocar no banco de dados!";
```

---

## 🌟 Como Isso Funciona em Frameworks (PSR-11 e DI Containers)

Em aplicações reais com dezenas de serviços, não precisamos instanciar as dependências manualmente toda vez (técnica conhecida como *Manual DI*). 

Utilizamos um **Container de Injeção de Dependência** em conformidade com a especificação **PSR-11**:

```php
// Configuração no Container de Serviços (ex: Laravel / PHP-DI):
$container->bind(DatabaseConnectionInterface::class, MySQLConnection::class);

// Resolução automática por Reflection:
// O framework inspeciona os parâmetros do construtor de OrderService e entrega o MySQLConnection pronto!
$orderService = $container->get(OrderService::class);
```

---

## 🧪 Como Rodar a Demonstração Prática

Para ver todos os cenários funcionando no seu terminal:

```bash
php principais-certificacoes/PHP-FIG/Design-Patterns/dependency-injection/dependency_injection.php
```
