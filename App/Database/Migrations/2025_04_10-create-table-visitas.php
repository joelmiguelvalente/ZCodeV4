<?php

# Migración para la tabla `visitas`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}visitas` (
		`id` INT AUTO_INCREMENT PRIMARY KEY,
		`user` INT NOT NULL,
		`for` INT NOT NULL,
		`type` TINYINT NOT NULL DEFAULT 0,
		`date` INT NOT NULL DEFAULT 0,
		`ip` VARBINARY(16) DEFAULT NULL,
		INDEX (`for`, `type`, `user`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
