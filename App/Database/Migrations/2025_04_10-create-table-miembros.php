<?php

# Migración para la tabla `miembros`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}miembros` (
		`user_id` INT AUTO_INCREMENT PRIMARY KEY,
		`user_activo` TINYINT NOT NULL DEFAULT 0,
		`user_amigos` BIGINT NOT NULL DEFAULT 0,
		`user_bad_hits` INT DEFAULT 0,
		`user_baneado` TINYINT NOT NULL DEFAULT 0,
		`user_cache` INT NOT NULL DEFAULT 0,
		`user_comentarios` BIGINT DEFAULT 0,
		`user_email` VARCHAR(255) UNIQUE NOT NULL,
		`user_verificado` TINYINT NOT NULL DEFAULT 0,
		`user_chat` INT NOT NULL DEFAULT 0,
		`user_secret_2fa` TEXT NULL,
		`user_recovery` TEXT NULL,
		`user_last_ip` VARBINARY(16) DEFAULT NULL,
		`user_lastactive` INT NOT NULL DEFAULT 0,
		`user_lastlogin` INT NOT NULL DEFAULT 0,
		`user_lastpost` INT NOT NULL DEFAULT 0,
		`user_name_changes` TINYINT UNSIGNED NOT NULL DEFAULT 3,
		`user_name` VARCHAR(50) UNIQUE NOT NULL,
		`user_nextpuntos` INT NOT NULL DEFAULT 0,
		`user_password` VARCHAR(255) NOT NULL,
		`user_posts` BIGINT DEFAULT 0,
		`user_puntos` BIGINT DEFAULT 0,
		`user_puntosxdar` INT DEFAULT 0,
		`user_rango` INT DEFAULT 3,
		`user_registro` INT NOT NULL DEFAULT 0,
		`user_outtime_type` TINYINT NOT NULL DEFAULT 0,
		`user_outtime_start` INT NOT NULL DEFAULT 0,
		`user_outtime` INT NOT NULL DEFAULT 0,
		`user_seguidores` BIGINT NOT NULL DEFAULT 0,
		`user_seguidos` BIGINT NOT NULL DEFAULT 0,
		INDEX idx_name (user_name),
		INDEX idx_email (user_email),
		INDEX idx_activo (user_activo),
		INDEX idx_baneado (user_baneado),
		INDEX idx_status (user_activo, user_baneado)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
