<?php

# Migración para la tabla `posts_supercategorias`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}posts_supercategorias` (
		`fid` INT AUTO_INCREMENT PRIMARY KEY,
		`super_nombre` VARCHAR(60) NOT NULL DEFAULT '',
		`super_descripcion` TEXT NULL,
		`super_color` VARCHAR(40) NOT NULL DEFAULT '',
		`super_img` VARCHAR(40) NOT NULL DEFAULT '',
		`super_orden` INT NOT NULL DEFAULT 0,
		`super_status` TINYINT NOT NULL DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
