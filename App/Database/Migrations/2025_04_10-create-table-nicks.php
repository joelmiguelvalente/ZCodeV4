<?php

# Migración para la tabla `nicks`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}nicks` (
		`id` INT AUTO_INCREMENT PRIMARY KEY,
		`user_id` INT NOT NULL,
		`name_1` VARCHAR(50) UNIQUE NOT NULL,
		`name_2` VARCHAR(50) UNIQUE NOT NULL,
		`user_email` VARCHAR(255) UNIQUE NOT NULL,
		`estado` TINYINT NOT NULL DEFAULT 0,
		`hash` VARCHAR(66) NOT NULL DEFAULT '',
		`ip` VARBINARY(16) DEFAULT NULL,
		`time` INT NOT NULL DEFAULT 0,
		INDEX idx_user_id (user_id),
		INDEX idx_estado (estado)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
