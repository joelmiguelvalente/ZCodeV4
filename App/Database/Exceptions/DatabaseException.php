<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
 */

declare(strict_types=1);

namespace App\Database\Exceptions;

use RuntimeException;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No direct script access allowed');
}

/**
 * Excepción específica para errores de Base de Datos
 */
class DatabaseException extends RuntimeException
{
    protected ?string $query;
    protected array $params;

    public function __construct(string $message, ?string $query = null, array $params = [], int $code = 0, ?\Throwable $previous = null)
    {
        $this->query  = $query;
        $this->params = $params;
        parent::__construct($message, $code, $previous);
    }

   /**
    * Devuelve la query que generó el error
    */
    public function getQuery(): ?string
    {
        return $this->query;
    }

   /**
    * Devuelve los parámetros usados en la consulta
    */
    public function getParams(): array
    {
        return $this->params;
    }

   /**
    * Devuelve toda la información en array (para logs)
    */
    public function toArray(): array
    {
        return [
         'message' => $this->getMessage(),
         'code'    => $this->getCode(),
         'query'   => $this->query,
         'params'  => $this->params,
         'file'    => $this->getFile(),
         'line'    => $this->getLine(),
         'trace'   => $this->getTrace(),
        ];
    }
}
