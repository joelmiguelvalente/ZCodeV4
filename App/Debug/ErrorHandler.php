<?php

/**
 * @package    ZCode
 * @author     Miguel92
 * @copyright  2024 - 2026
 * @version    4.0.0
*/

declare(strict_types=1);

namespace App\Debug;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No direct script access allowed');
}

class ErrorHandler
{
    private static array $buffer = [];
    public static bool $debug = true;
    private static bool $rendered = false;

    public static function register(bool $debug = true): void
    {
         self::$debug = $debug;

         set_error_handler([self::class, 'handleError']);
         set_exception_handler([self::class, 'handleException']);
         register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
         self::addToBuffer('Error', $errstr, $errfile, $errline, $errno);
         return true;
    }

    public static function handleException(\Throwable $ex): void
    {
         self::addToBuffer(
             'Exception',
             $ex->getMessage(),
             $ex->getFile(),
             $ex->getLine(),
             $ex->getCode()
         );

         self::render();
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error) {
            self::addToBuffer(
                'Shutdown Error',
                $error['message'],
                $error['file'],
                $error['line'],
                $error['type']
            );
        }

        if (!empty(self::$buffer)) {
            self::render();
        }
    }


    private static function addToBuffer(string $title, string $message, string $file, int $line, int $type): void
    {
         error_log("[$title:$type] $message in $file:$line");

        if (!self::$debug) {
            return;
        }

         $color = self::colorFromType($type);

         self::$buffer[] = compact('title', 'message', 'file', 'line', 'type', 'color');
    }

    private static function render(): void
    {
        if (self::$rendered) {
            return;
        }

        self::$rendered = true;
        // Limpia todo el output previo
        if (ob_get_length()) {
            ob_clean();
        }
        require_once __DIR__ . '/helper.php';

        $errors = self::$buffer;
        foreach ($errors as $k => $err) {
            $errors[$k]['id'] = 'err_' . md5($err['file'] . $err['line'] . $k . microtime(true));
        }
        $layout = __DIR__ . '/views/layout.php';

        if (file_exists($layout)) {
             require $layout;
        } else {
            foreach ($errors as $err) {
                  echo "<div style='background:{$err['color']};padding:15px;margin:10px 0;'>";
                  echo "<h3>{$err['title']}</h3>";
                  echo "<p>{$err['message']}</p>";
                  echo "<small>{$err['file']}:{$err['line']}</small>";
                  echo "</div>";
            }
        }

        exit;
    }


    private static function colorFromType(int $type): string
    {
         return match ($type) {
               E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR => '#d9534f',
               E_WARNING, E_USER_WARNING => '#f0ad4e',
               E_NOTICE, E_USER_NOTICE => '#5bc0de',
               E_DEPRECATED, E_USER_DEPRECATED => '#999999',
               default => '#cccccc',
         };
    }
}
