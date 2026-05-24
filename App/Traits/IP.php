<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

namespace App\Traits;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

/**
 * Este trait asume que las propiedades $core y $user son inyectadas
 * desde la clase que lo utiliza, típicamente vía constructor.
 */

trait IP
{
   /**
     * Funci�n privada para validar la IP del usuario
    */
    private function isValidIP(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Funci�n para obtener la IP del usuario
    */
    public function getIP(): string
    {
        $ip = 'unknown';
        // List of trusted proxy IP headers
        $trustedHeaders = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($trustedHeaders as $header) {
            if (isset($_SERVER[$header]) && $this->isValidIP($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                break;
            }
        }
        return $ip;
    }

    /**
     * Funci�n para validar y obtener la direcci�n IP del cliente que realiza la petici�n.
     *
     * @return string|null La direcci�n IP v�lida del cliente o NULL si no se puede validar.
    */
    public function validarIP()
    {
        $_SERVER['REMOTE_ADDR'] = $_SERVER['X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Obtiene la IP real del usuario de manera segura.
     * No modifica ninguna superglobal.
     */
    public function executeIP(): string
    {
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!isset($_SERVER[$header])) {
                continue;
            }

            // X_FORWARDED_FOR puede traer múltiples IPs, tomamos la primera
            $ipList = explode(',', $_SERVER[$header]);
            $ipList = array_map('trim', $ipList);

            foreach ($ipList as $ip) {
                if ($this->isValidIP($ip)) {
                    return $ip;
                }
            }
        }

        // Si nada sirve, devolvemos unknown o lanzamos excepción a elección
        return 'unknown';
    }
}
