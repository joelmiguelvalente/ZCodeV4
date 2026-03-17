<?php

# Migración para la tabla `perfil_avatar`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}perfil_avatar` (
		`uavatar_id` INT PRIMARY KEY,
		`uavatar_gif` VARCHAR(255) NOT NULL DEFAULT '',
		`uavatar_gif_active` TINYINT NOT NULL DEFAULT 0,
		`uavatar_type` TINYINT NOT NULL DEFAULT 0, /* Tipo gif, normal, social */
		`uavatar_social` CHAR(20) NOT NULL DEFAULT 'web', /* Nombre de red social */
		`uavatar_use` CHAR(32) NOT NULL DEFAULT '' /* Nombre del avatar actual */
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];
