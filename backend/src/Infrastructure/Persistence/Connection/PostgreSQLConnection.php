<?php

declare(strict_types=1);

namespace Src\Infrastructure\Persistence\Connection;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Implementação de conexão com PostgreSQL utilizando PDO.
 * 
 * Demonstra a facilidade de troca de driver viabilizada pelo padrão Dependency Injection.
 */
class PostgreSQLConnection implements DatabaseConnectionInterface 
{
    private ?PDO $pdo = null;
    private bool $connected = false;
    private ?string $lastError = null;

    public function __construct(
        ?PDO $pdo = null,
        private string $host = '127.0.0.1',
        private string $database = 'guia_desenvolvimento_software',
        private string $username = 'postgres',
        private string $password = '',
        private int $port = 5432
    ) {
        if ($pdo !== null) {
            $this->pdo = $pdo;
            $this->connected = true;
            return;
        }

        $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->database}";

        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $this->connected = true;
        } catch (PDOException $e) {
            $this->connected = false;
            $this->lastError = $e->getMessage();
        }
    }

    public function query(string $sql, array $params = []): array 
    {
        if ($this->pdo === null) {
            echo "🐘 [PostgreSQLConnection - Log]: Executando consulta: {$sql}\n";
            return [];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function execute(string $sql, array $params = []): int 
    {
        if ($this->pdo === null) {
            echo "🐘 [PostgreSQLConnection - Log]: Executando comando: {$sql}\n";
            return 1;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function getPdo(): ?PDO 
    {
        return $this->pdo;
    }

    public function isConnected(): bool 
    {
        return $this->connected;
    }
}
