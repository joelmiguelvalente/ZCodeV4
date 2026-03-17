<?php

# Migración para la tabla `historial`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}historial` (
		`id` INT AUTO_INCREMENT PRIMARY KEY,
		`pofid` INT DEFAULT NULL,
		`type` TINYINT NOT NULL DEFAULT 0,
		`action` TINYINT NOT NULL DEFAULT 0,
		`mod` INT DEFAULT NULL,
		`reason` TEXT NULL,
		`date` INT NOT NULL DEFAULT 0,
		`mod_ip` VARBINARY(16) DEFAULT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
