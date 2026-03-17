<?php

# Migración para la tabla `miembros_social`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}miembros_social` (
		`social_id` INT AUTO_INCREMENT PRIMARY KEY,
		`social_user_id` INT DEFAULT 0,
		`social_name` CHAR(20) NOT NULL DEFAULT '',
		`social_nick` CHAR(24) NOT NULL DEFAULT '',
		`social_email` VARCHAR(80) NOT NULL DEFAULT '',
		`social_avatar` VARCHAR(255) NOT NULL DEFAULT ''
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
