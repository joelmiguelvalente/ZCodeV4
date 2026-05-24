<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

namespace App\Database\Adapter;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No direct script access allowed');
}

use App\Database\interfaces\DatabaseInterface;
use App\Database\exceptions\DatabaseException;

class PDOAdapter implements DatabaseInterface
{
    private \PDO $pdo;
    private array $config;
    private bool $connected = false;

   /** Prefix como "zcode_" */
    private string $prefix = '';

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->prefix = $config['prefix'] ?? '';
    }

   /** Permite actualizar el prefix desde DB::init */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * Reemplaza @tabla → prefix_tabla
     * Seguro: ignora parámetros y placeholders.
     */
    private function applyPrefix(string $sql): string
    {
        if ($this->prefix === '') {
            return $sql;
        }
        return preg_replace_callback('/@([a-zA-Z0-9_]+)/', fn($m) => $this->prefix . $m[1], $sql);
    }

    public function connect(): void
    {
        if ($this->connected) {
            return;
        }
        $options = $this->config['options'] ?? [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new \PDO(
                $this->config['dsn'],
                $this->config['username'] ?? null,
                $this->config['password'] ?? null,
                $options
            );
            $this->connected = true;
        } catch (\PDOException $e) {
            throw new DatabaseException("No se pudo establecer conexión PDO: " . $e->getMessage(), null, [], (int) $e->getCode(), $e);
        }
    }

    public function close(): void
    {
        $this->pdo = null;
        $this->connected = false;
    }

    private function normalizeParams(string $sql, array $params = []): array
    {
        if (empty($params)) {
            return [$sql, []];
        }
        $assoc = array_keys($params) !== range(0, count($params) - 1);
        if (!$assoc) {
            return [$sql, array_values($params)];
        }

        preg_match_all('/:([a-zA-Z0-9_]+)/', $sql, $matches);
        $placeholders = $matches[1];
        if (empty($placeholders)) {
            throw new DatabaseException(
                "Se pasaron parámetros con nombre, pero la sentencia no contiene placeholders nombrados."
            );
        }

        $ordered = [];
        foreach ($placeholders as $ph) {
            if (!array_key_exists($ph, $params)) {
                throw new DatabaseException("Falta el valor para :{$ph}");
            }
            $ordered[] = $params[$ph];
            $sql = preg_replace('/:' . preg_quote($ph, '/') . '\b/', '?', $sql, 1);
        }
        return [$sql, $ordered];
    }

    private function castParams(array $params): array
    {
        $out = [];

        foreach ($params as $p) {
            if (is_int($p) || (is_string($p) && ctype_digit($p))) {
                $out[] = (int)$p;
                continue;
            }
            if (is_float($p) || (is_string($p) && is_numeric($p))) {
                $out[] = (float)$p;
                continue;
            }
            if (is_null($p)) {
                $out[] = null;
                continue;
            }
            $out[] = (string)$p;
        }
        return $out;
    }

    /**
     * ESTE método es el corazón del adapter.
     * Aquí aplicamos el prefix ANTES de preparar la consulta.
     */
    private function stmtExecute(string $sql, array $params = []): \PDOStatement
    {
        // Aplicar prefix
        $sql = $this->applyPrefix($sql);
        [$sql, $params] = $this->normalizeParams($sql, $params);
        $params = $this->castParams($params);
        $stmt = $this->pdo->prepare($sql);
        if (!$stmt->execute($params)) {
            $err = $stmt->errorInfo();
            throw new DatabaseException('Error al ejecutar statement: ' . $err[2]);
        }

        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->stmtExecute($sql, $params);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->stmtExecute($sql, $params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->stmtExecute($sql, $params);
        return $stmt->rowCount();
    }

    public function lastInsertId(): ?string
    {
        try {
            return $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            return null;
        }
    }

    public function rowCount(string $sql, array $params = []): int
    {
        $stmt = $this->stmtExecute($sql, $params);
        return $stmt->rowCount();
    }

    public function fetchRow(string $sql, array $params = []): ?array
    {
        $stmt = $this->stmtExecute($sql, $params);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function error(): ?string
    {
        $info = $this->pdo->errorInfo();
        return $info[2] ?? null;
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    public function importSQLFile(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            throw new DatabaseException("Archivo SQL no encontrado: {$filePath}");
        }
        $sql = file_get_contents($filePath);
        if ($sql === false || trim($sql) === '') {
            throw new DatabaseException("Archivo vacío: {$filePath}");
        }
        $queries = preg_split('/;(?=(?:[^\'"]|\'[^\']*\'|"[^"]*")*$)/', $sql);
        $this->pdo->beginTransaction();
        try {
            foreach ($queries as $query) {
                $query = trim($query);
                if ($query === '') {
                    continue;
                }
                // Aquí también aplicamos prefix
                $query = $this->applyPrefix($query);
                $this->pdo->exec($query);
            }
            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw new DatabaseException("Error ejecutando {$filePath}: " . $e->getMessage());
        }
    }

    public function escape(string $value): string
    {
        return substr($this->pdo->quote($value), 1, -1);
    }

    public function update(string $table, array $data, array $where): int
    {
        if (empty($data)) {
            throw new DatabaseException('UPDATE sin datos');
        }
        if (empty($where)) {
            throw new DatabaseException('UPDATE sin WHERE no permitido');
        }
       // SET dinámico
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "`$column` = :set_$column";
        }
       // WHERE dinámico (múltiples condiciones)
        $conditions = [];
        foreach ($where as $column => $value) {
            $conditions[] = "`$column` = :where_$column";
        }
        $sql = sprintf("UPDATE @%s SET %s WHERE %s", $table, implode(', ', $set), implode(' AND ', $conditions));
       // Parámetros
        $params = [];
        foreach ($data as $k => $v) {
            $params["set_$k"] = $v;
        }
        foreach ($where as $k => $v) {
            $params["where_$k"] = $v;
        }
        return $this->execute($sql, $params);
    }

    public function insert(string $table, array $data): int
    {
        if (empty($data)) {
            throw new DatabaseException('INSERT sin datos');
        }
        $columns = [];
        $placeholders = [];
        $params = [];
        foreach ($data as $column => $value) {
            $columns[] = "`{$column}`";
            $placeholders[] = ":{$column}";
            $params[$column] = $value;
        }
        $sql = sprintf("INSERT INTO @%s (%s) VALUES (%s)", $table, implode(', ', $columns), implode(', ', $placeholders));
        return $this->execute($sql, $params);
    }
}
