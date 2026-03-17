<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
 */

declare(strict_types=1);

namespace App\Services;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class LoggerService
{
    private string $file;

    public function setLogFile(string $file = 'app.log'): void
    {
        $this->file = dirname(__DIR__, 2) . '/storage/logs/' . $file;
    }

    public function info(string $message): void
    {
        $this->write("INFO", $message);
    }

    public function warning(string $message): void
    {
        $this->write("WARNING", $message);
    }

    public function error(string $message): void
    {
        $this->write("ERROR", $message);
    }

    private function normalize(mixed $message): string
    {
        if (is_array($message) || is_object($message)) {
            return print_r($message, true);
        }
        if (is_bool($message)) {
            return $message ? 'true' : 'false';
        }
        return (string) $message;
    }

    private function write(string $level, string $message): void
    {
        if (!isset($this->file)) {
            throw new \RuntimeException("Log file path not set in Logger trait.");
        }

        $format = '[%s] [%s] %s%s';
        $date   = date('Y-m-d H:i:s');
        $entry  = sprintf($format, $date, $level, $this->normalize($message), PHP_EOL);

        file_put_contents($this->file, $entry, FILE_APPEND);
    }
}
