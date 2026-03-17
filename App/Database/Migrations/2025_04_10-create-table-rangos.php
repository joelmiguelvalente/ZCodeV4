<?php

# Migración para la tabla `rangos`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}rangos` (
		`rango_id` INT PRIMARY KEY AUTO_INCREMENT,
		`r_allows` VARCHAR(1000) NOT NULL DEFAULT '',
		`r_cant` INT NOT NULL DEFAULT 0,
		`r_color` CHAR(12) NOT NULL DEFAULT '171717',
		`r_image` VARCHAR(32) NOT NULL DEFAULT 'new.png',
		`r_name` VARCHAR(32) NOT NULL DEFAULT '',
		`r_type` INT NOT NULL DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
