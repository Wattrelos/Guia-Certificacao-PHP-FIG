<?php

declare(strict_types=1);

namespace Src\Infrastructure\Persistence\Connection;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Implementação de conexão com MySQL / MariaDB utilizando PDO.
 * 
 * Demonstra a Injeção de Dependência através do Construtor (Constructor Injection):
 * - Pode receber uma instância pré-existente de PDO (ideal para testes ou conexões compartilhadas).
 * - Ou receber as credenciais e parâmetros de conexão configurados externamente (ex: do .env).
 * - Suporta prefixo dinâmico de tabelas (DB_PREFIX, ex: agsc_).
 * - Possui método de fábrica createFromEnv() para leitura automática do backend/.env.
 */
class MySQLConnection implements DatabaseConnectionInterface 
{
    private ?PDO $pdo = null;
    private bool $connected = false;
    private ?string $lastError = null;
    private string $prefix = '';

    /**
     * @var array<string, mixed>
     */
    private array $config = [];

    /**
     * Construtor com Injeção de Dependências.
     *
     * @param PDO|null $pdo Instância opcional de PDO já conectada
     * @param string|null $host Host do servidor MySQL (padrão: 127.0.0.1)
     * @param string|null $database Nome do banco de dados
     * @param string|null $username Usuário do banco
     * @param string|null $password Senha de acesso
     * @param int $port Porta de rede (padrão: 3306)
     * @param string|null $prefix Prefixo das tabelas (padrão: lido do .env ou agsc_)
     * @param array<int, mixed> $options Opções extras do driver PDO
     */
    public function __construct(
        ?PDO $pdo = null,
        ?string $host = null,
        ?string $database = null,
        ?string $username = null,
        ?string $password = null,
        int $port = 3306,
        ?string $prefix = null,
        array $options = []
    ) {
        if ($pdo !== null) {
            $this->pdo = $pdo;
            $this->connected = true;
            $this->prefix = $prefix ?? $_ENV['DB_PREFIX'] ?? 'agsc_';
            $this->config = ['driver' => 'mysql', 'source' => 'injected_pdo', 'prefix' => $this->prefix];
            return;
        }

        // Se nenhum dado for passado, tenta carregar as variáveis do backend/.env
        if ($host === null && !isset($_ENV['DB_HOST'])) {
            self::loadEnv(dirname(__DIR__, 4) . '/.env');
        }

        $host     = $host ?? $_ENV['DB_HOST'] ?? $_ENV['DB_HOSTNAME'] ?? '127.0.0.1';
        $database = $database ?? $_ENV['DB_DATABASE'] ?? 'guia_desenvolvimento_software';
        $username = $username ?? $_ENV['DB_USERNAME'] ?? 'desenvolvedor';
        $password = $password ?? $_ENV['DB_PASSWORD'] ?? '';
        $port     = $port !== 3306 ? $port : (int) ($_ENV['DB_PORT'] ?? 3306);
        $this->prefix = $prefix ?? $_ENV['DB_PREFIX'] ?? 'agsc_';

        $this->config = [
            'driver'   => 'mysql',
            'host'     => $host,
            'port'     => $port,
            'prefix'   => $this->prefix,
            'database' => $database,
            'username' => $username,
        ];

        $defaultOptions = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdoOptions = $options ?: $defaultOptions;
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        try {
            $this->pdo = new PDO($dsn, $username, $password, $pdoOptions);
            $this->connected = true;
        } catch (PDOException $e) {
            $this->connected = false;
            $this->lastError = $e->getMessage();
        }
    }

    /**
     * Fábrica estática para criar a conexão a partir de um arquivo .env específico.
     */
    public static function createFromEnv(?string $envPath = null): self 
    {
        $path = $envPath ?? dirname(__DIR__, 4) . '/.env';
        self::loadEnv($path);

        return new self(
            pdo: null,
            host: $_ENV['DB_HOST'] ?? $_ENV['DB_HOSTNAME'] ?? '127.0.0.1',
            database: $_ENV['DB_DATABASE'] ?? 'guia_desenvolvimento_software',
            username: $_ENV['DB_USERNAME'] ?? 'desenvolvedor',
            password: $_ENV['DB_PASSWORD'] ?? '',
            port: (int) ($_ENV['DB_PORT'] ?? 3306),
            prefix: $_ENV['DB_PREFIX'] ?? 'agsc_'
        );
    }

    /**
     * Retorna o prefixo configurado para as tabelas.
     */
    public function getPrefix(): string 
    {
        return $this->prefix;
    }

    /**
     * Retorna o nome da tabela com o prefixo adicionado (ex: 'user' -> 'agsc_user').
     */
    public function tableName(string $table): string 
    {
        return $this->prefix . $table;
    }

    /**
     * Executa uma consulta SQL com prepared statements.
     *
     * @param string $sql
     * @param array<string|int, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    public function query(string $sql, array $params = []): array 
    {
        if ($this->pdo === null) {
            echo "⚡ [MySQLConnection - Log]: Executando consulta: {$sql}\n";
            return [];
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            throw new RuntimeException("Falha ao executar query no MySQL: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Executa comandos de alteração de estado (INSERT, UPDATE, DELETE).
     */
    public function execute(string $sql, array $params = []): int 
    {
        if ($this->pdo === null) {
            echo "⚡ [MySQLConnection - Log]: Executando comando: {$sql}\n";
            return 1;
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            throw new RuntimeException("Falha ao executar comando no MySQL: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function getPdo(): ?PDO 
    {
        return $this->pdo;
    }

    public function isConnected(): bool 
    {
        return $this->connected;
    }

    public function getLastError(): ?string 
    {
        return $this->lastError;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array 
    {
        return $this->config;
    }

    /**
     * Parser nativo para carregar arquivos .env sem depender de bibliotecas externas.
     */
    private static function loadEnv(string $filePath): void 
    {
        if (!file_exists($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $val = trim($parts[1]);

                if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                    (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                    $val = substr($val, 1, -1);
                }

                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $val;
                    putenv("{$key}={$val}");
                }
            }
        }
    }
}
