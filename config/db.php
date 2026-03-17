<?php

/**
 * PHPost Risus & ZCode 2026
 *
 * Archivo de configuración principal para la conexión a la base de datos.
 * Devuelve un arreglo asociativo con los parámetros necesarios para establecer la conexión
 * mediante el adaptador definido en 'driver'.
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
    * Controlador del sistema de base de datos a utilizar.
    * Ejemplos posibles: mysql, pdo
    */
   'driver'   => 'mysql',

	/**
	 * Nombre del servidor o dirección IP del host de la base de datos.
	 * Comúnmente "localhost" o la IP del servidor remoto.
	 */
   'hostname' => $_ENV['DB_HOST'],

	/**
	 * Usuario con permisos de acceso a la base de datos.
	 * Recomendado: usar un usuario con privilegios limitados por seguridad.
	 */
   'username' => $_ENV['DB_USER'],

	/**
	 * Contraseña asociada al usuario de la base de datos.
	 * Nota: nunca subir este archivo a un repositorio público.
	 */
   'password' => $_ENV['DB_PASS'],

	/**
	 * Nombre de la base de datos a la cual se conectará el sistema.
	 */
   'database' => $_ENV['DB_NAME'],

	/**
	 * Puerto utilizado por el servidor de base de datos.
	 * Por defecto MySQL usa el puerto 3306.
	 */
   'port'     => $_ENV['DB_PORT'] ?? 3306,

	/**
	 * Codificación de caracteres utilizada por la conexión.
	 * utf8mb4 soporta todos los caracteres Unicode, incluidos emojis.
	 */
   'charset'  => $_ENV['DB_CHARSET'] ?? 'utf8mb4',

	/**
	 * Definimos el prefijo para las tablas
	 */
   'prefix'   => $_ENV['DB_PREFIX'] ?? '',

	/**
	 * Intercalación de caracteres (collation) asociada al conjunto de caracteres.
	 * Define cómo se comparan y ordenan los textos en la base de datos.
	 */
	'collation' => 'utf8mb4_unicode_ci'

];
