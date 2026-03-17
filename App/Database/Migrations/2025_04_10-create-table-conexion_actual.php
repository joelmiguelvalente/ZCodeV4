<?php

# Migración para la tabla `conexion_actual`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}conexion_actual` (
		`id` INT AUTO_INCREMENT PRIMARY KEY,
		`ip` VARBINARY(16) DEFAULT NULL,
		`session_id` VARCHAR(255) NOT NULL DEFAULT '',
		`last_activity` INT NOT NULL DEFAULT 0,
		UNIQUE KEY (ip, session_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
