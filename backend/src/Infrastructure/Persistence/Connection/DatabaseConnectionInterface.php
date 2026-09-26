<?php

declare(strict_types=1);

namespace Src\Infrastructure\Persistence\Connection;

use PDO;

/**
 * Contrato (Interface) de Conexão com o Banco de Dados.
 * 
 * Segue o Princípio de Inversão de Dependência (DIP - SOLID) e as recomendações
 * da PSR-1/PSR-12 para nomenclatura e tipagem estrita no PHP moderno.
 * 
 * A camada de aplicação/serviço deve depender exclusivamente desta interface,
 * nunca de implementações concretas (como MySQLConnection ou PostgreSQLConnection).
 */
interface DatabaseConnectionInterface 
{
    /**
     * Executa uma consulta SQL (SELECT, UPDATE, INSERT, etc.) com suporte a prepared statements.
     *
     * @param string $sql Instrução SQL a ser executada
     * @param array<string|int, mixed> $params Parâmetros opcionais para binding seguro
     * @return array<int, array<string, mixed>> Registros retornados (ou array vazio para comandos de escrita)
     */
    public function query(string $sql, array $params = []): array;

    /**
     * Executa um comando SQL de mutação (INSERT, UPDATE, DELETE) e retorna as linhas afetadas.
     *
     * @param string $sql Instrução SQL
     * @param array<string|int, mixed> $params Parâmetros opcionais
     * @return int Total de linhas afetadas
     */
    public function execute(string $sql, array $params = []): int;

    /**
     * Retorna a instância nativa do PDO (se aplicável), permitindo transações avançadas.
     */
    public function getPdo(): ?PDO;
}