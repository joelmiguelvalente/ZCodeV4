<?php

namespace App\Database;

use Exception;
use Throwable;
use App\Database\DB;

class Runner
{
    private string $prefix;

    public function __construct(string $prefix = '')
    {
        $this->prefix = $prefix;
    }

    public function run(string $folder): array
    {
        $log = [];
        $prefix = $this->prefix;
        $directory = __DIR__ . "/{$folder}";

        foreach (glob("$directory/*.php") as $file) {
            try {
                $queries = require $file;

                foreach ($queries as $sql) {
                    // En caso que necesites reemplazar {{prefix}}
                    $sql = str_replace('{{prefix}}', $prefix, $sql);

                    DB::execute($sql); // Ahora usamos tu clase DB
                }

                $log[] = [
                    'file'   => basename($file),
                    'status' => 'success',
                    'date'   => date('Y-m-d'),
                ];
            } catch (Throwable $e) {
                $log[] = [
                    'file'  => basename($file),
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }

        return $log;
    }

    public function runAndLog(string $folder, string $logFile): array
    {
        $logFile = __DIR__ . "/$logFile";
        if (file_exists($logFile)) {
            unlink($logFile);
        }

        $result = $this->run($folder);
        $this->saveLog($result, $logFile);

        return $result;
    }

    public function saveLog(array $log, string $filename): void
    {
        file_put_contents(
            $filename,
            json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
