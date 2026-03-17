<?php

# Migración para la tabla `contacts`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}contacts` (
		`id` INT AUTO_INCREMENT PRIMARY KEY,
		`user_id` INT NOT NULL,
		`user_email` VARCHAR(255) UNIQUE NOT NULL,
		`time` INT NOT NULL DEFAULT 0,
		`type` TINYINT NOT NULL DEFAULT 0,
		`hash` CHAR(128) NOT NULL DEFAULT '',
		`ip` VARBINARY(16) DEFAULT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
