<?php

# Migración para la tabla `sessions`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}sessions` (
		`session_id` CHAR(32) PRIMARY KEY DEFAULT '',
		`session_user_id` INT UNSIGNED NOT NULL DEFAULT 0,
		`session_ip` VARBINARY(16) DEFAULT NULL,
		`session_token` CHAR(100) NOT NULL DEFAULT '',
		`session_time` INT NOT NULL DEFAULT 0,
		`session_autologin` TINYINT NOT NULL DEFAULT 0,
		KEY `session_user_id` (`session_user_id`),
		KEY `session_time` (`session_time`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];
