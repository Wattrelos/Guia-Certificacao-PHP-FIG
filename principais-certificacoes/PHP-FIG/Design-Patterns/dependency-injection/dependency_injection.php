<?php

declare(strict_types=1);

/**
 * ==============================================================================
 * DEMONSTRAÇÃO PRÁTICA: INJEÇÃO DE DEPENDÊNCIA (DEPENDENCY INJECTION)
 * ==============================================================================
 * 
 * Este script demonstra como o padrão Dependency Injection (DI) substitui
 * abordagens rígidas (como o Singleton ou new acoplado), permitindo:
 * 
 * 1. Desacoplamento através de Interfaces (DIP - Dependency Inversion Principle).
 * 2. Conexão real com MySQL usando as variáveis do arquivo backend/.env.
 * 3. Alternância instantânea de tecnologia (MySQL -> PostgreSQL) sem refatorar o serviço.
 * 4. Testabilidade máxima com Mocks/In-Memory sem precisar de banco de dados rodando.
 */

// Importação das classes de infraestrutura
require_once __DIR__ . '/../../../../backend/src/Infrastructure/Persistence/Connection/DatabaseConnectionInterface.php';
require_once __DIR__ . '/../../../../backend/src/Infrastructure/Persistence/Connection/MySQLConnection.php';
require_once __DIR__ . '/../../../../backend/src/Infrastructure/Persistence/Connection/PostgreSQLConnection.php';
require_once __DIR__ . '/../../../../backend/src/Infrastructure/Persistence/Connection/InMemoryConnection.php';

use Src\Infrastructure\Persistence\Connection\DatabaseConnectionInterface;
use Src\Infrastructure\Persistence\Connection\MySQLConnection;
use Src\Infrastructure\Persistence\Connection\PostgreSQLConnection;
use Src\Infrastructure\Persistence\Connection\InMemoryConnection;

/**
 * Serviço de Negócio: OrderService
 * 
 * Note que a classe NÃO cria conexões com "new MySQLConnection()"
 * nem busca instâncias globais com "DatabaseConnection::getInstance()".
 * Em vez disso, ela declara no construtor o contrato que precisa.
 */
class OrderService 
{
    /**
     * Injeção de Dependência via Construtor (Constructor Injection).
     * O PHP moderno (PHP 8+) permite a promoção de propriedades de forma concisa.
     */
    public function __construct(
        private DatabaseConnectionInterface $db
    ) {}

    public function checkout(int $orderId): bool 
    {
        echo "🛒 [OrderService]: Processando checkout do pedido #{$orderId}...\n";

        // Executa a atualização usando o contrato da interface
        $sql = "UPDATE orders SET status = 'paid', updated_at = NOW() WHERE id = :id";
        $this->db->query($sql, ['id' => $orderId]);

        echo "✅ [OrderService]: Pedido #{$orderId} pago com sucesso!\n";
        return true;
    }
}

// ==============================================================================
// EXECUÇÃO DOS CENÁRIOS DIDÁTICOS
// ==============================================================================

echo "====================================================================\n";
echo " DEMONSTRAÇÃO: PADRÃO INJEÇÃO DE DEPENDÊNCIA (DI) NO PHP MODERNO     \n";
echo "====================================================================\n\n";

// ------------------------------------------------------------------------------
// CENÁRIO 1: AMBIENTE DE PRODUÇÃO COM MYSQL E DADOS DO BACKEND/.ENV
// ------------------------------------------------------------------------------
echo "--- CENÁRIO 1: Produção com MySQL (Dados lidos de backend/.env) ---\n";

// Cria a conexão lendo diretamente o backend/.env
$mysql = MySQLConnection::createFromEnv();
$config = $mysql->getConfig();

echo "• Host: " . ($config['host'] ?? '127.0.0.1') . ":" . ($config['port'] ?? 3306) . "\n";
echo "• Database: " . ($config['database'] ?? '') . "\n";
echo "• Username: " . ($config['username'] ?? '') . "\n";

if ($mysql->isConnected()) {
    echo "• Status PDO: Conexão física ativa no servidor MySQL/MariaDB!\n";
} else {
    echo "• Status PDO: Credenciais do .env carregadas com sucesso.\n";
    echo "  (Nota: " . ($mysql->getLastError() ?: 'Servidor aguardando criação do usuário') . ")\n";
}

// Injetamos a conexão concreta do MySQL no OrderService
$orderServiceProd = new OrderService($mysql);
$orderServiceProd->checkout(101);
echo "\n";

// ------------------------------------------------------------------------------
// CENÁRIO 2: MUDANÇA DE DRIVER PARA POSTGRESQL (ZERO MUDANÇAS NO ORDERSERVICE)
// ------------------------------------------------------------------------------
echo "--- CENÁRIO 2: Troca de Driver para PostgreSQL ---\n";
echo "Imagine que a empresa decidiu migrar do MySQL para o PostgreSQL.\n";
echo "A classe OrderService NÃO precisa ser alterada em NENHUMA linha!\n\n";

$postgres = new PostgreSQLConnection(
    host: '127.0.0.1',
    database: 'guia_desenvolvimento_software',
    username: 'postgres',
    password: '',
    port: 5432
);

// Injetamos a implementação PostgreSQL no OrderService
$orderServicePostgres = new OrderService($postgres);
$orderServicePostgres->checkout(202);
echo "\n";

// ------------------------------------------------------------------------------
// CENÁRIO 3: TESTES UNITÁRIOS COM MOCK/IN-MEMORY (100% ISOLADO, 0 BANCO DE DADOS)
// ------------------------------------------------------------------------------
echo "--- CENÁRIO 3: Teste Unitário Automatizado (Sem Banco de Dados) ---\n";
echo "Aqui vemos a maior vantagem sobre o Singleton: o desacoplamento permite\n";
echo "testar a regra de negócio sem precisar de servidores ou bancos reais ativos.\n\n";

$inMemoryDb = new InMemoryConnection();

// Injetamos o Mock/In-Memory no OrderService
$orderServiceTest = new OrderService($inMemoryDb);
$orderServiceTest->checkout(303);

// Asserção do Teste:
if ($inMemoryDb->hasExecuted("UPDATE orders SET status = 'paid'")) {
    echo "\n🎉 [TESTE PASSOU]: A consulta esperada de pagamento foi disparada corretamente!\n";
} else {
    echo "\n❌ [TESTE FALHOU]: A query de pagamento não foi identificada.\n";
}

echo "====================================================================\n";
