<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

namespace App\Database;

if (! defined('ZCODE_ULTIMATE')) {
    exit('No direct script access allowed');
}

use App\Database\Interfaces\DatabaseInterface;
use App\Database\Adapter\{PDOAdapter,MySQLiAdapter};

/**
 * Clase estática Database
 *
 * Actúa como fachada universal que abstrae el uso de PDO o MySQLi.
 * Crea, inicializa y expone todos los métodos del adaptador activo,
 * permitiendo escribir código limpio sin necesidad de usar "global".
 */
final class DB
{
    private static ?DatabaseInterface $adapter = null;

    /**
     * Inicializa el adaptador de base de datos según la configuración.
     */
    public static function init(array $config): void
    {
        if (self::$adapter !== null) {
            return;
        }

        if ($config['driver'] === 'pdo') {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;port=%d;charset=%s",
                $config['hostname'],
                $config['database'],
                $config['port'] ?? 3306,
                $config['charset'] ?? 'utf8mb4'
            );

            self::$adapter = new PDOAdapter([
                'dsn' => $dsn,
                'username' => $config['username'],
                'password' => $config['password'],
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ],
            ]);
        } else {
            self::$adapter = new MySQLiAdapter($config);
        }

        self::$adapter->connect();
    }

    /** Retorna el adaptador activo (PDOAdapter o MySQLiAdapter). */
    public static function get(): DatabaseInterface
    {
        if (!self::$adapter) {
            throw new RuntimeException('La base de datos no está inicializada. Usa DB::init().');
        }
        return self::$adapter;
    }

    /** Cierra la conexión actual. */
    public static function close(): void
    {
        self::get()->close();
        self::$adapter = null;
    }

    // -------------------------------------------------------
    // MÉTODOS PRINCIPALES (equivalentes a los de los adaptadores)
    // -------------------------------------------------------

    public static function execute(string $sql, array $params = []): int
    {
        return self::get()->execute($sql, $params);
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        return self::get()->fetch($sql, $params);
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::get()->fetchAll($sql, $params);
    }

    public static function rowCount(string $sql, array $params = []): int
    {
        return self::get()->rowCount($sql, $params);
    }

    public static function lastInsertId(): ?int
    {
        return self::get()->lastInsertId();
    }

    public static function error(): ?string
    {
        return self::get()->error();
    }

    public static function beginTransaction(): bool
    {
        return self::get()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::get()->commit();
    }

    public static function rollBack(): bool
    {
        return self::get()->rollBack();
    }

    public static function escape(string $string): string
    {
        return self::get()->escape($string);
    }

    public static function importSQLFile(string $file): bool
    {
        return self::get()->importSQLFile($file);
    }

    // -------------------------------------------------------
    // MÉTODOS SIMPLIFICADOS (alias más legibles)
    // -------------------------------------------------------


    public static function update(string $table, array $data, array $where): int
    {
        return self::get()->update($table, $data, $where);
    }

    public static function insert(string $table, array $data): int
    {
        return self::get()->insert($table, $data);
    }
}
