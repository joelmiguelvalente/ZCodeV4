<?php

# Migración para la tabla `fotos_album`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}fotos_album` (
		`aid` INT AUTO_INCREMENT PRIMARY KEY,
		`a_name` VARCHAR(60) NOT NULL DEFAULT '',
		`a_cover` VARCHAR(255) NOT NULL DEFAULT '',
		`a_description` VARCHAR(255) DEFAULT NULL,
		`a_status` TINYINT NOT NULL DEFAULT 0,
		`a_date` INT NOT NULL DEFAULT 0,
		`a_update` INT NOT NULL DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
