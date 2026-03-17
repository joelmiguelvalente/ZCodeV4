<?php

# Migración para la tabla `posts_categorias`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}posts_categorias` (
		`cid` INT AUTO_INCREMENT PRIMARY KEY,
		`c_orden` INT NOT NULL,
		`c_foro` INT NOT NULL,
		`c_nombre` VARCHAR(50) NOT NULL DEFAULT '',
		`c_seo` VARCHAR(50) NOT NULL DEFAULT '',
		`c_img` VARCHAR(50) NOT NULL DEFAULT '',
		`c_color` CHAR(12) NOT NULL DEFAULT '',
		`c_descripcion` TEXT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
