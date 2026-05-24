<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

namespace App\Database\Interfaces;

if (! defined('ZCODE_ULTIMATE')) {
    exit('No direct script access allowed');
}

use App\Database\exceptions\DatabaseException;

/**
 * Interfaz simple y agnóstica para la base de datos.
 */
interface DatabaseInterface
{
    public function connect(): void;
    public function close(): void;

   /** Ejecuta una consulta SELECT y devuelve un único registro (assoc) o null */
    public function fetch(string $sql, array $params = []): ?array;

   /** Ejecuta una consulta SELECT y devuelve todos los registros (array of assoc) */
    public function fetchAll(string $sql, array $params = []): array;

   /** Ejecuta una consulta (INSERT/UPDATE/DELETE/otro) y devuelve filas afectadas */
    public function execute(string $sql, array $params = []): int;

    public function lastInsertId(): ?int;
    public function rowCount(string $sql, array $params = []): int;
    public function fetchRow(string $sql, array $params = []): ?array;

    public function beginTransaction(): bool;
    public function commit(): bool;
    public function rollBack(): bool;
    public function escape(string $string): string;
    public function update(string $table, array $data, array $where): int;
    public function insert(string $table, array $data): int;
}
