<?php

# Migración para la tabla `social`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}social` (
		`social_id` INT AUTO_INCREMENT PRIMARY KEY,
		`social_name` CHAR(22) NOT NULL DEFAULT '',
		`social_client_id` VARCHAR(255) NOT NULL DEFAULT '',
		`social_client_secret` VARCHAR(255) NOT NULL DEFAULT '',
		`social_redirect_uri` VARCHAR(255) NOT NULL DEFAULT '',
		`social_status` TINYINT NOT NULL DEFAULT 0,
		`social_icon` VARCHAR(20) NOT NULL DEFAULT '',
		INDEX idx_status (social_status),
		INDEX idx_name (social_name)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
