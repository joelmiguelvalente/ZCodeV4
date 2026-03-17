<?php

# Migración para la tabla `sitemap_control`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}sitemap_control` (
		`sid` INT PRIMARY KEY DEFAULT 0,
		`register_post` TINYINT NOT NULL DEFAULT 0,
		`register_foto` TINYINT NOT NULL DEFAULT 0,
		`register_comunidades` TINYINT NOT NULL DEFAULT 0,
		`register_temas` TINYINT NOT NULL DEFAULT 0,
		`register_respuestas` TINYINT NOT NULL DEFAULT 0,
		`update_post` TINYINT NOT NULL DEFAULT 0,
		`update_foto` TINYINT NOT NULL DEFAULT 0,
		`update_comunidades` TINYINT NOT NULL DEFAULT 0,
		`update_temas` TINYINT NOT NULL DEFAULT 0,
		`update_respuestas` TINYINT NOT NULL DEFAULT 0,
		INDEX idx_sid (sid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];
