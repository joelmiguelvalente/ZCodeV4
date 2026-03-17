<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.0.0
*/

declare(strict_types=1);

namespace App\Core\Security;

class Signature
{
    private string $domain;
    private string $localKey;

    public function __construct(string $domain, string $localKey)
    {
        $this->domain   = $domain;
        $this->localKey = $localKey;
    }

    public static function generateLocalKey(): string
    {
        return bin2hex(random_bytes(32)); // 64 chars
    }

    public function getTimestamp(): int
    {
        return time();
    }

    public function verify(array $data, int $timestamp, string $receivedSignature): bool
    {
        $expected = $this->sign($data, $timestamp);

        return hash_equals($expected, $receivedSignature);
    }


    public function sign(array $data, ?int $timestamp = null): string
    {
        $timestamp ??= $this->getTimestamp();

        ksort($data);

        $payload = $this->domain . '|' . $timestamp . '|' . http_build_query($data);

        return hash_hmac('sha256', $payload, $this->localKey);
    }

    public function headers(array $data): array
    {
        $timestamp = $this->getTimestamp();
        $signature = $this->sign($data, $timestamp);

        return [
           'X-ZCode-Domain'    => $this->domain,
           'X-ZCode-Timestamp' => $timestamp,
           'X-ZCode-Signature' => $signature
        ];
    }
}

/* Ejemplo de uso
$signature = new Signature($_SERVER['HTTP_HOST'], ZCODE_LOCAL_KEY);

$headers = $signature->headers([
   'request' => 'themes'
]);
*/
