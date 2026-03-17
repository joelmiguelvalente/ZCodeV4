<?php

/**
 * @package    ZCode
 * @author     Miguel92
 * @copyright  2024 - 2026
 * @version    4.0.0
 */

declare(strict_types=1);

namespace App\Http\Middlewares;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class SanitizeRequestMiddleware
{
    public function handle($tsCore, $tsUser, $smarty): void
    {
        $this->validarGlobals();
        $this->validarReferer();
        $this->validarClaves();
        $this->sanitizarEntradas();
        $this->sanitizarSuperglobals();
    }

    private function validarGlobals(): void
    {
        if (isset($_REQUEST['GLOBALS']) || isset($_COOKIE['GLOBALS'])) {
            http_response_code(400);
            exit('Solicitud no válida: acceso a GLOBALS');
        }
    }

    private function validarReferer(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $refererHost = parse_url($referer, PHP_URL_HOST);

        if (!empty($referer) && $refererHost && $refererHost !== $host && $_SERVER['REQUEST_METHOD'] === 'POST') {
            http_response_code(403);
            exit('Solicitud no autorizada.');
        }
    }

    private function validarClaves(): void
    {
        $fuentes = [$_GET, $_POST, $_COOKIE, $_FILES];

        foreach ($fuentes as $fuente) {
            foreach (array_keys($fuente) as $key) {
                if (is_numeric($key)) {
                    http_response_code(400);
                    exit('Claves numéricas no permitidas.');
                }
            }
        }
    }

    private function cleanValue($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->cleanValue($v);
            }
            return $value;
        }
        return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
    }


    private function sanitizarEntradas(): void
    {
        $_GET    = filter_input_array(INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? [];
        $_POST   = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? [];
        $_COOKIE = filter_input_array(INPUT_COOKIE, FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? [];

        // sincronizar REQUEST
        $_REQUEST = $_POST + $_GET;
    }

    private function sanitizarSuperglobals(): void
    {
        foreach (['_POST', '_GET', '_COOKIE'] as $super) {
            foreach ($GLOBALS[$super] as $key => $value) {
                $GLOBALS[$super][$key] = $this->cleanValue($value);
            }
        }
    }
}
