<?php

declare(strict_types=1);

namespace Src\Infrastructure\Persistence\Connection;

use PDO;

/**
 * Implementação Mock / Em Memória de DatabaseConnectionInterface.
 * 
 * Permite rodar testes unitários em milissegundos sem depender de nenhum
 * serviço de banco de dados rodando na máquina.
 */
class InMemoryConnection implements DatabaseConnectionInterface 
{
    /**
     * @var array<int, string> Histórico de comandos e queries executadas para assert em testes
     */
    private array $executedQueries = [];

    /**
     * @var array<int, array<string, mixed>> Resultados pré-configurados para mock
     */
    private array $stubbedResults = [];

    private string $prefix = 'agsc_';

    public function __construct(array $stubbedResults = [], string $prefix = 'agsc_') 
    {
        $this->stubbedResults = $stubbedResults;
        $this->prefix = $prefix;
    }

    public function getPrefix(): string 
    {
        return $this->prefix;
    }

    public function tableName(string $table): string 
    {
        return $this->prefix . $table;
    }

    public function query(string $sql, array $params = []): array 
    {
        $this->executedQueries[] = $sql;
        echo "🧪 [InMemoryConnection - Mock]: Query registrada para teste: {$sql}\n";
        return $this->stubbedResults;
    }

    public function execute(string $sql, array $params = []): int 
    {
        $this->executedQueries[] = $sql;
        echo "🧪 [InMemoryConnection - Mock]: Comando registrado para teste: {$sql}\n";
        return 1;
    }

    public function getPdo(): ?PDO 
    {
        return null;
    }

    /**
     * @return array<int, string>
     */
    public function getExecutedQueries(): array 
    {
        return $this->executedQueries;
    }

    public function hasExecuted(string $sqlFragment): bool 
    {
        foreach ($this->executedQueries as $query) {
            if (str_contains($query, $sqlFragment)) {
                return true;
            }
        }
        return false;
    }
}
