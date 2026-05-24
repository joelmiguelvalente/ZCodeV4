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

/**
 * Adapter MySQLi con soporte de prefix (@tabla → prefix_tabla)
 */
class MySQLiAdapter implements DatabaseInterface
{
    private \mysqli $mysqli;
    private array $config;
    private bool $connected = false;
    private string $prefix = '';

    public function __construct(array $config)
    {
        $this->config  = $config;
        $this->prefix  = $config['prefix'] ?? '';
    }

   /** Permite cambiar prefix dinámicamente */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

   /** Asegura conexión interna */
    private function ensureConnection(): void
    {
        if (!$this->connected) {
            $this->connect();
        }
    }

   /** Reemplaza @tabla por prefix_tabla */
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

        $host   = $this->config['hostname'] ?? '127.0.0.1';
        $user   = $this->config['username'] ?? '';
        $pass   = $this->config['password'] ?? '';
        $db     = $this->config['database'] ?? '';
        $port   = $this->config['port'] ?? 3306;

        $this->mysqli = mysqli_init();

        $this->mysqli->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);


        if (!$this->mysqli->real_connect($host, $user, $pass, $db, (int)$port)) {
            throw new DatabaseException('MySQLi connect error: ' . $this->mysqli->connect_error);
        }

        $this->connected = true;
        $this->mysqli->set_charset($this->config['charset'] ?? 'utf8mb4');
    }

    public function close(): void
    {
        $this->mysqli->close();
        $this->connected = false;
    }

    private function isDirectQuery(string $sql): bool
    {
        return preg_match('/^\s*(CHECK|ANALYZE|REPAIR|OPTIMIZE|SHOW|DESCRIBE|EXPLAIN|TRUNCATE)\s+/i', $sql) === 1;
    }

   /** Convierte placeholders :nombre → ? */
    private function normalizeParams(string $sql, array $params = []): array
    {
        if (empty($params)) {
            return [$sql, []];
        }
        $assoc = array_keys($params) !== range(0, count($params) - 1);
        preg_match_all('/:([a-zA-Z0-9_]+)/', $sql, $matches);
        $placeholders = $matches[1];

        if ($assoc && empty($placeholders)) {
            return [$sql, array_values($params)];
        }
        $orderedParams = [];
        foreach ($placeholders as $ph) {
            if (!array_key_exists($ph, $params)) {
                throw new DatabaseException("Falta el valor para :{$ph}");
            }
            $orderedParams[] = $params[$ph];
           // Se reemplazan todas las apariciones del placeholder
            $sql = preg_replace('/:' . preg_quote($ph, '/') . '\b/', '?', $sql);
        }
        return [$sql, $orderedParams];
    }

   /** Prepara y ejecuta el statement */
    private function stmtExecute(string $sql, array $params = []): \mysqli_stmt
    {
        $this->ensureConnection();

       // 1. prefix
        $sql = $this->applyPrefix($sql);

       // 2. normalización placeholders
        [$sql, $params] = $this->normalizeParams($sql, $params);
        //
        $stmt = $this->mysqli->prepare($sql);
        if ($stmt === false) {
            throw new DatabaseException("Error al preparar la consulta", $sql, $params, $this->mysqli->errno);
        }
        if (!empty($params)) {
            $types = '';
            $values = [];
            foreach ($params as $p) {
                if (is_int($p)) {
                    $types .= 'i';
                } elseif (is_float($p)) {
                    $types .= 'd';
                } elseif (is_null($p)) {
                    $types .= 's';
                } else {
                    $types .= 's';
                }
                $values[] = $p;
            }
            $refs = [];
            foreach ($values as $k => $v) {
                $refs[$k] = &$values[$k];
            }
            array_unshift($refs, $types);
            if (!call_user_func_array([$stmt, 'bind_param'], $refs)) {
                throw new DatabaseException('fallo del bind_param: ' . $stmt->error);
            }
        }

        if (!$stmt->execute()) {
            throw new DatabaseException('Ejecucion fallida: ' . $stmt->error);
        }

        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->stmtExecute($sql, $params);

        if (method_exists($stmt, 'get_result')) {
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $res->free();
            $stmt->close();
            return $row ?: null;
        }
       // fallback
        $meta = $stmt->result_metadata();
        if (!$meta) {
            $stmt->close();
            return null;
        }
        $row = [];
        $bind = [];
        while ($field = $meta->fetch_field()) {
            $bind[] = &$row[$field->name];
        }
        $meta->free();

        call_user_func_array([$stmt, 'bind_result'], $bind);
        $stmt->fetch();
        $stmt->close();

        return $row ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->stmtExecute($sql, $params);
        $results = [];

        if (method_exists($stmt, 'get_result')) {
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $results[] = $row;
            }
            $res->free();
            $stmt->close();
            return $results;
        }
       // fallback
        $meta = $stmt->result_metadata();
        if (!$meta) {
            $stmt->close();
            return [];
        }
        $row = [];
        $bind = [];
        while ($field = $meta->fetch_field()) {
            $bind[] = &$row[$field->name];
        }
        $meta->free();
        call_user_func_array([$stmt, 'bind_result'], $bind);

        while ($stmt->fetch()) {
            $results[] = $row;
        }

        $stmt->close();
        return $results;
    }

    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->stmtExecute($sql, $params);
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }

    public function lastInsertId(): ?int
    {
        $id = $this->mysqli->insert_id;
        return $id > 0 ? $id : null;
    }

    public function rowCount(string $sql, array $params = []): int
    {
        $stmt = $this->stmtExecute($sql, $params);
        if (method_exists($stmt, 'get_result')) {
            $res = $stmt->get_result();
            $count = $res->num_rows;
            $res->free();
            $stmt->close();
            return $count;
        }
        $all = $this->fetchAll($sql, $params);
        return count($all);
    }

    public function fetchRow(string $sql, array $params = []): ?array
    {
        $stmt = $this->stmtExecute($sql, $params);

        if (method_exists($stmt, 'get_result')) {
            $res = $stmt->get_result();
            $row = $res->fetch_assoc() ?: null;
            $res->free();
            $stmt->close();
            return $row;
        }

       // fallback: bind_result
        $result = [];
        $meta = $stmt->result_metadata();
        if ($meta) {
            $fields = $meta->fetch_fields();
            $bindVars = [];
            foreach ($fields as $field) {
                $bindVars[] = &$result[$field->name];
            }
            call_user_func_array([$stmt, 'bind_result'], $bindVars);
            $stmt->fetch();
        }
        $stmt->close();
        return $result ?: null;
    }

    public function error(): ?string
    {
        return $this->mysqli->error ?: null;
    }

    public function beginTransaction(): bool
    {
        return $this->mysqli->begin_transaction();
    }

    public function commit(): bool
    {
        return $this->mysqli->commit();
    }

    public function rollBack(): bool
    {
        return $this->mysqli->rollback();
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
        foreach ($queries as $query) {
            $query = trim($query);
            if ($query === '') {
                continue;
            }
            $query = $this->applyPrefix($query);
            try {
                $this->execute($query);
            } catch (\Throwable $e) {
                throw new DatabaseException("Error ejecutando {$filePath}: " . $e->getMessage());
            }
        }
        return true;
    }

    public function escape(string $value): string
    {
        return $this->mysqli->real_escape_string($value);
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
       // WHERE dinámico (soporta múltiples condiciones)
        $conditions = [];
        foreach ($where as $column => $value) {
            $conditions[] = "`$column` = :where_$column";
        }
        $sql = sprintf("UPDATE @%s SET %s WHERE %s", $table, implode(', ', $set), implode(' AND ', $conditions));

       // Merge de parámetros
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
            $columns[] = "`$column`";
            $placeholders[] = ":$column";
            $params[$column] = $value;
        }
        $sql = sprintf("INSERT INTO @%s (%s) VALUES (%s)", $table, implode(', ', $columns), implode(', ', $placeholders));
        return $this->execute($sql, $params);
    }
}
