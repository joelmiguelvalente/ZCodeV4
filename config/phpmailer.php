<?php

/**
 * PHPost Risus & ZCode 2026
 *
 * Archivo de configuración para los parámetros de conexión SMTP.
 * Devuelve un arreglo asociativo con la información necesaria para enviar
 * correos electrónicos mediante el servidor definido en estas variables.
 *
 * @package   ZCode
 * @author    Miguel92
 * @copyright 2024 - 2026
 * @license   Propiedad de su autor, uso permitido con fines educativos y de desarrollo.
 * @version   4.0.0
 */

declare(strict_types=1);

return [

	/**
	 * Host del servidor SMTP responsable del envío de correos.
	 * Comúnmente "smtp.gmail.com", "smtp.mailgun.org" o un host personalizado.
	 */
	'smtp_host' => $_ENV['SMTP_HOST'],

	/**
	 * Nombre de usuario para autenticar la conexión SMTP.
	 * Suele corresponder al correo utilizado para enviar mensajes.
	 */
	'smtp_user' => $_ENV['SMTP_USER'],

	/**
	 * Contraseña o token de aplicación usado para autenticarse en el servidor SMTP.
	 * Nota importante: este valor nunca debe publicarse en repositorios.
	 */
	'smtp_pass' => $_ENV['SMTP_PASS'],
	
	/**
	 * Nombre que aparecerá como remitente visible
	 * ("From: Nombre <correo@dominio.com>").
	 */
	'smtp_name' => $_ENV['SMTP_NAME'],

	/**
	 * Puerto usado para la conexión SMTP.
	 * Puertos comunes: 587 (TLS), 465 (SSL), 25 (sin cifrado).
	 */
	'smtp_port' => $_ENV['SMTP_PORT'] ?? 587,

	/**
	 * Protocolo de seguridad usado en la conexión.
	 * Ejemplos: tls, ssl.
	 */
	'smtp_secure' => 'tls'

];