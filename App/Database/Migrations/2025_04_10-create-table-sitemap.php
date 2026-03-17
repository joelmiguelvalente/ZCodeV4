<?php

# Migración para la tabla `sitemap`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}sitemap` (
		`id` INT PRIMARY KEY AUTO_INCREMENT,
		`url` VARCHAR(255) NULL,
		`frecuencia` VARCHAR(15) NOT NULL DEFAULT '',
		`fecha` INT NOT NULL DEFAULT 0,
		`prioridad` DECIMAL(2,1) NOT NULL DEFAULT 0,
		INDEX idx_url (url)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;"
];
