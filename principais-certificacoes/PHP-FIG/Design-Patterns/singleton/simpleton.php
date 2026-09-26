<?php

declare(strict_types=1);

/**
 * Função utilitária para carregar variáveis de um arquivo .env no PHP nativo
 * (sem dependência obrigatória do Composer / vlucas/phpdotenv para execuções didáticas).
 */
function loadEnv(string $filePath): void 
{
    if (!file_exists($filePath)) {
        throw new RuntimeException("Arquivo de ambiente (.env) não encontrado em: {$filePath}");
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);

        // Ignora comentários e linhas em branco
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        // Divide apenas no primeiro '='
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);

            // Remove aspas simples ou duplas que envolvam o valor
            if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                $val = substr($val, 1, -1);
            }

            // Define em $_ENV e putenv se ainda não configurado
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $val;
                putenv("{$key}={$val}");
            }
        }
    }
}

// Carrega o arquivo .env compartilhado do diretório de Design-Patterns
loadEnv(__DIR__ . '/../.env');

/**
 * Classe DatabaseConnection implementando o padrão de projeto Singleton.
 * 
 * Garante que exista apenas uma instância da conexão com o banco de dados
 * durante toda a execução da aplicação e fornece um ponto de acesso global.
 */
class DatabaseConnection 
{
    // 1. Armazena a única instância da classe nesta propriedade estática privada
    private static ?DatabaseConnection $instance = null;
    
    private string $connectionId;
    private ?PDO $pdo = null;
    private bool $connected = false;
    private ?string $errorMessage = null;

    /**
     * @var array<string, mixed> Armazena os parâmetros carregados do .env (sem expor senha)
     */
    private array $config = [];

    // 2. Construtor PRIVADO: impede que a classe seja instanciada com "new" externamente
    private function __construct() 
    {
        $this->connectionId = uniqid('conn_', true);

        // Leitura das variáveis carregadas do .env
        $driver = strtolower($_ENV['DB_CONNECTION'] ?? 'mysql');
        $host   = $_ENV['DB_HOST'] ?? $_ENV['DB_HOSTNAME'] ?? '127.0.0.1';
        $port   = $_ENV['DB_PORT'] ?? ($driver === 'pgsql' ? '5432' : '3306');
        $db     = $_ENV['DB_DATABASE'] ?? 'guia_desenvolvimento_software';
        $user   = $_ENV['DB_USERNAME'] ?? 'desenvolvedor';
        $pass   = $_ENV['DB_PASSWORD'] ?? '';

        $this->config = [
            'driver'   => $driver,
            'host'     => $host,
            'port'     => $port,
            'database' => $db,
            'username' => $user,
        ];

        // Montagem do DSN de acordo com o driver configurado no .env
        $dsn = match ($driver) {
            'pgsql'            => "pgsql:host={$host};port={$port};dbname={$db}",
            'sqlite'           => "sqlite:{$db}",
            'mysql', 'mariadb' => "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
            default            => "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
        };

        // Opções recomendadas para conexão segura e robusta via PDO
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            // Instanciação real do PDO utilizando os dados do .env
            $this->pdo = new PDO($dsn, $user, $pass, $options);
            $this->connected = true;
        } catch (PDOException $e) {
            $this->connected = false;
            $this->errorMessage = $e->getMessage();
        }
    }

    // 3. Método clone PRIVADO impede a duplicação do objeto com clone
    private function __clone(): void {}

    // 4. Métodos de serialização: no PHP 8+, devem ser públicos e lançar exceção para impedir unserialize
    public function __wakeup(): void 
    {
        throw new Exception("Não é permitido desserializar uma instância de Singleton.");
    }

    public function __unserialize(array $data): void 
    {
        throw new Exception("Não é permitido desserializar uma instância de Singleton.");
    }

    // 5. Ponto de acesso global: cria ou retorna a instância única existente
    public static function getInstance(): DatabaseConnection 
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnectionId(): string 
    {
        return $this->connectionId;
    }

    public function getPdo(): ?PDO 
    {
        return $this->pdo;
    }

    public function isConnected(): bool 
    {
        return $this->connected;
    }

    public function getErrorMessage(): ?string 
    {
        return $this->errorMessage;
    }

    public function getConfig(): array 
    {
        return $this->config;
    }
}

// ==========================================
// TESTANDO O SINGLETON
// ==========================================

echo "=====================================================\n";
echo " TESTE DO PADRÃO SINGLETON COM CARREGAMENTO DO .ENV  \n";
echo "=====================================================\n\n";

// $dbInvalido = new DatabaseConnection(); // ❌ Erro fatal! Construtor é privado.

// 1. Obtendo a primeira instância
$db1 = DatabaseConnection::getInstance();
echo "[Instância 1] ID: " . $db1->getConnectionId() . "\n";

// 2. Obtendo a segunda instância
$db2 = DatabaseConnection::getInstance();
echo "[Instância 2] ID: " . $db2->getConnectionId() . "\n\n";

// 3. Verificação de identidade: ambos apontam para o mesmo endereço de memória?
if ($db1 === $db2) {
    echo "✅ Sucesso do Singleton: Ambas as variáveis usam a mesma e única instância!\n\n";
} else {
    echo "❌ Falha: Instâncias distintas foram criadas!\n\n";
}

// 4. Exibindo as configurações que foram lidas diretamente do .env
echo "--- Dados de Conexão Lidos do Arquivo .env ---\n";
foreach ($db1->getConfig() as $chave => $valor) {
    echo "• " . ucfirst($chave) . ": {$valor}\n";
}
echo "\n";

// 5. Verificação da conexão física do PDO
if ($db1->isConnected()) {
    echo "✅ Conexão PDO: Estabelecida com sucesso com o banco de dados!\n";
} else {
    echo "⚠️ Nota da Conexão PDO: As credenciais do .env foram lidas corretamente, mas a conexão física retornou:\n";
    echo "   -> " . $db1->getErrorMessage() . "\n";
    echo "   (Dica: Para estabelecer a conexão física completa, certifique-se de que o usuário e a base existam no MySQL/MariaDB).\n";
}

echo "=====================================================\n";
