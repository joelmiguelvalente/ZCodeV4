<?php

# Migración para la tabla `perfil`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}perfil` (
		`user_id` INT PRIMARY KEY,
		`user_dia` TINYINT DEFAULT 0,
		`user_mes` TINYINT DEFAULT 0,
		`user_ano` SMALLINT DEFAULT 0,
		`user_pais` CHAR(2) NOT NULL DEFAULT '',
		`user_estado` TINYINT NOT NULL DEFAULT 1,
		`user_sexo` CHAR(10) NOT NULL DEFAULT 'none',
		`user_firma` VARCHAR(255) NOT NULL DEFAULT '',
		`user_gif` VARCHAR(255) NOT NULL DEFAULT '',
		`user_gif_active` TINYINT NOT NULL DEFAULT 0,
		`user_avatar_type` TINYINT NOT NULL DEFAULT 0, /* Tipo gif, normal, social */
		`user_avatar_social` CHAR(20) NOT NULL DEFAULT 'web', /* Nombre de red social */
		`user_portada` VARCHAR(255) DEFAULT NULL,
		`user_scheme` TINYINT NOT NULL DEFAULT 0,
		`user_color` TINYINT NOT NULL DEFAULT 1,
		`user_customize` CHAR(20) NOT NULL DEFAULT '#212121;#F4F4F4',
		`user_font_family` CHAR(30) NOT NULL DEFAULT 'tema',
		`user_font_size` CHAR(3) NOT NULL DEFAULT 'md',
		`user_pagebox` TINYINT NOT NULL DEFAULT 0,
		`p_nombre` VARCHAR(100) DEFAULT NULL,
		`p_avatar` TINYINT NOT NULL DEFAULT 0,
		`p_mensaje` TEXT DEFAULT NULL,
		`p_sitio` VARCHAR(255) DEFAULT NULL,
		`p_socials` TEXT DEFAULT NULL,
		`p_configs` VARCHAR(180) NOT NULL DEFAULT 'a:3:{s:1:\"m\";s:1:\"5\";s:2:\"mf\";i:5;s:3:\"rmp\";s:1:\"5\";}',
		`p_total` VARCHAR(54) NOT NULL DEFAULT 'a:6:{i:0;i:5;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;}'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];
