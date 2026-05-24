<?php

declare(strict_types=1);

namespace Install\src\utils;

use RuntimeException;
use InvalidArgumentException;

/**
 * Class Helpers
 *
 * Utilidades generales del sistema.
 * Esta clase está enfocada en seguridad, URL handling, entorno y helpers comunes.
 *
 * @package src\utils
 * @version 4.1.0
 * @author Miguel
 */
final class Helpers
{
    public const VERSION = '4.1.0';
    private const TYPE_NAME = 'name';
    private const TYPE_FULL = 'full';
    private const TYPE_CODE = 'code';

    public static function version(string $type = self::TYPE_FULL): string
    {
        $complete = 'ZCode v' . self::VERSION;
        return match ($type) {
            self::TYPE_NAME => 'ZCode',
            self::TYPE_FULL => $complete,
            self::TYPE_CODE => self::slugify($complete, '_'),
            default => self::VERSION
        };
    }

    public static function setTitle(): string
    {
        $page = match (self::getCurrentStep()) {
            'database' => 'Base de datos',
            'sitio' => 'Datos del sitio',
            'fallo' => 'Error desconocido',
            default => ucfirst(self::getCurrentStep())
        };
        return self::version(self::TYPE_FULL) . ' | ' . $page;
    }

    /**
     * Convierte un texto en un slug seguro para URL.
     *
     * @param string $text
     * @return string
     */
    public static function slugify(string $text, string $separator = '-'): string
    {
        if ($text === '') {
            return '';
        }

        $text = preg_replace('~[^\pL\d]+~u', $separator, $text) ?? '';
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        $text = preg_replace('~[^-\w]+~', '', $text) ?? '';
        $text = trim($text, $separator);
        $text = preg_replace('~-+~', $separator, $text) ?? '';

        return strtolower($text);
    }

    /**
     * Reemplaza claves en un archivo .env basado en un sample
     *
     * @param array<string,string> $pairs
     * @return bool
     */
    public static function replaceInEnv(array $pairs): bool
    {
        $environment = dirname(__DIR__, 3) . '/.env';
        $source = file_exists($environment) ? $environment : "{$environment}.example";

        if (!file_exists($source)) {
            throw new RuntimeException("Archivo origen no encontrado: {$source}");
        }

        $content = file_get_contents($source);

        if ($content === false) {
            throw new RuntimeException("No se pudo leer el archivo: {$source}");
        }

        foreach ($pairs as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        return file_put_contents($environment, $content) !== false;
    }

    /**
     * Verifica si la aplicación está en entorno local
     *
     * @return bool
     */
    public static function isLocalhost(): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        return in_array($ip, ['127.0.0.1', '::1'], true);
    }

    /**
     * Genera datos de instalación o seguridad
     *
     * @param string $item
     * @param int $bytes
     * @return string
     */
    public static function generateInstall(string $item = 'ID', int $bytes = 16): string
    {
        $data = [
            'MODE'   => self::isLocalhost() ? 'DEVELOPMENT' : 'PRODUCTION',
            'ID'     => bin2hex(random_bytes(16)),
            'SECRET' => bin2hex(random_bytes(32)),
            'KEY'    => strtoupper(bin2hex(random_bytes($bytes)))
        ];

        if (!array_key_exists(strtoupper($item), $data)) {
            throw new InvalidArgumentException("Elemento inválido solicitado: {$item}");
        }

        return $data[strtoupper($item)];
    }

    /**
     * Retorna un estado UI para uso en views
     *
     * @param bool $state
     * @param string $label
     * @return array{class: string, text: string}
     */
    public static function makeStatus(bool $state, string $label): array
    {
        return [
            'class' => $state ? 'success' : 'danger',
            'text'  => $state ? $label : "No {$label}"
        ];
    }

    /**
     * Obtiene el esquema HTTP/HTTPS
     *
     * @param bool $withSeparator
     * @return string
     */
    public static function getScheme(bool $withSeparator = false): string
    {
        $https = (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (($_SERVER['SERVER_PORT'] ?? null) == 443) ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        );

        $scheme = $https ? 'https' : 'http';

        return $scheme . ($withSeparator ? '://' : '');
    }

    /**
     * Obtiene el host
     *
     * @return string
     */
    public static function getHost(): string
    {
        return $_SERVER['HTTP_HOST']
            ?? $_SERVER['SERVER_NAME']
            ?? 'localhost';
    }

    /**
     * Obtiene el root del proyecto sin incluir /install
     *
     * @return string
     */
    public static function getRootPath(): string
    {
        $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

        return str_ends_with($path, '/install')
            ? substr($path, 0, -8)
            : $path;
    }

    /**
     * Obtiene la URL base completa
     *
     * @param bool $withScheme
     * @return string
     */
    public static function getBaseUrl(bool $withScheme = true): string
    {
        $scheme = $withScheme ? self::getScheme(true) : '';
        $host   = self::getHost();
        $path   = self::getRootPath();

        return rtrim("{$scheme}{$host}{$path}", '/');
    }

    private static function matchMethod(string $type = ''): mixed
    {
        return match (strtolower($type)) {
            'get'  => $_GET,
            'post' => $_POST,
            default => throw new InvalidArgumentException("Método inválido: {$type}")
        };
    }

    /**
     * Valida un método HTTP y un valor específico en GET o POST
     *
     * @param string $key
     * @param string|null $value
     * @param string $type
     * @return bool
     */
    public static function isMethod(string $key, ?string $value = null, string $type = 'get'): bool
    {
        $data = self::matchMethod($type);
        return isset($data[$key]) && ($value === null || $data[$key] === $value);
    }

    /**
     * Obtiene un valor desde GET o POST de forma segura
     *
     * @param string      $key     Clave a buscar.
     * @param string      $type    'get' o 'post'.
     * @param mixed|null  $default Valor por defecto si no existe.
     * @return mixed|null
     */
    public static function getMethod(string $key, string $type = 'get', mixed $default = null): mixed
    {
        $data = self::matchMethod($type);
        return $data[$key] ?? $default;
    }

    /**
     * Firma una petición usando HMAC SHA256
     *
     * @param string $installId
     * @param string $secret
     * @param int $timestamp
     * @return string
     */
    public static function signRequest(string $installId, string $secret, int $timestamp): string
    {
        return hash_hmac('sha256', $installId . $timestamp, $secret);
    }

    /**
     * Obtiene el paso actual de la instalación a partir del parámetro GET
     *
     * @param array  $allowed  Lista de pasos permitidos
     * @param string $default  Paso por defecto si no es válido
     * @param string $param    Nombre del parámetro GET
     * @return string           Paso validado
     */
    public static function getCurrentStep(
        array $allowed = ['bienvenida', 'licencia', 'requisitos', 'database', 'sitio', 'phpmailer', 'administrador', 'finalizar', 'fallo'],
        string $default = 'fallo',
        string $param = 'action'
    ): string {
        $action = $_GET[$param] ?? $default;

        $action = trim((string) $action);
        $action = strtolower($action);

        if (!in_array($action, $allowed, true)) {
            return $default;
        }

        return $action;
    }

    private static function getControllerView(string $basepath = '', string $type = '', array $msg = [], string $tpl = '')
    {
        $filename = empty($tpl) ? self::getCurrentStep() : $tpl;

        if (!$basepath || !is_dir($basepath)) {
            throw new \RuntimeException($msg['no_disponible']);
        }

        $file = $basepath . DIRECTORY_SEPARATOR . "{$type}.{$filename}.php";

        if (!is_file($file)) {
            throw new \RuntimeException(
                str_replace('{filename}', $filename, $msg['no_existe'])
            );
        }
        return $file;
    }

    /**
     * Obtiene la ruta absoluta del controlador correspondiente al paso actual
     *
     * @return string Ruta completa del controlador
     * @throws \RuntimeException Si el directorio o el archivo no existen
     */
    public static function controller(): string
    {
        $path = realpath(dirname(__DIR__, 1) . '/controllers');
        return self::getControllerView($path, 'controller', [
            'no_disponible' => 'Directorio de controladores no disponible.',
            'no_existe' => 'El controlador {filename} no existe.'
        ]);
    }

    /**
     * Obtiene la ruta absoluta del controlador correspondiente al paso actual
     *
     * @return string Ruta completa del controlador
     * @throws \RuntimeException Si el directorio o el archivo no existen
     */
    public static function view(string $tpl = ''): string
    {
        $path = realpath(dirname(__DIR__, 2) . '/views');
        return self::getControllerView($path, 'view', [
            'no_disponible' => 'Directorio de vista no disponible.',
            'no_existe' => 'La vista {filename} no existe.'
        ], $tpl);
    }

    public static function isEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function isValidNick(string $nick, int $min = 8, int $max = 20): bool
    {
        $len = strlen($nick);

        if ($len < $min || $len > $max) {
            return false;
        }

       // A-Z, a-z, 0-9, _ y -
        return preg_match('/^[a-zA-Z0-9_-]+$/', $nick) === 1;
    }

    public static function isStrongPassword(string $password, int $minLength = 8): bool
    {
        if (strlen($password) < $minLength) {
            return false;
        }

        $hasUpper   = preg_match('/[A-Z]/', $password);          // al menos 1 mayúscula
        $hasNumber  = preg_match('/[0-9]/', $password);          // al menos 1 número
        $hasSymbol  = preg_match('/[\W_]/', $password);          // al menos 1 símbolo

        return $hasUpper && $hasNumber && $hasSymbol;
    }
}
