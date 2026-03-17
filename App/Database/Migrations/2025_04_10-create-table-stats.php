<?php

# Migración para la tabla `stats`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}stats` (
		`stats_no` INT PRIMARY KEY DEFAULT 0,
		`stats_max_online` BIGINT NOT NULL DEFAULT 0,
		`stats_max_time` INT NOT NULL DEFAULT 0,
		`stats_time` INT NOT NULL DEFAULT 0,
		`stats_time_cache` INT NOT NULL DEFAULT 0,
		`stats_time_foundation` INT NOT NULL DEFAULT 0,
		`stats_time_upgrade` INT NOT NULL DEFAULT 0,
		`stats_miembros` BIGINT NOT NULL DEFAULT 0,
		`stats_posts` BIGINT NOT NULL DEFAULT 0,
		`stats_fotos` BIGINT NOT NULL DEFAULT 0,
		`stats_comments` BIGINT NOT NULL DEFAULT 0,
		`stats_foto_comments` BIGINT NOT NULL DEFAULT 0,
		`stats_comunidades` BIGINT NOT NULL DEFAULT 0,
		`stats_temas` BIGINT NOT NULL DEFAULT 0,
		`stats_respuestas` BIGINT NOT NULL DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];
