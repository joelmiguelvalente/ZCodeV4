<?php

# Migración para la tabla `actividad`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}actividad` (
		`ac_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		`ac_date` INT NOT NULL DEFAULT 0,
		`ac_type` TINYINT UNSIGNED NOT NULL DEFAULT 0,
		`obj_uno` INT UNSIGNED NOT NULL DEFAULT 0,
		`obj_dos` INT UNSIGNED NOT NULL DEFAULT 0,
		`user_id` INT UNSIGNED NOT NULL,
		INDEX (`ac_type`),
		INDEX (`user_id`),
		INDEX (`ac_date`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];
