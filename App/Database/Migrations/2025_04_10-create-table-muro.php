<?php

# Migración para la tabla `muro`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}muro` (
		`pub_id` INT AUTO_INCREMENT PRIMARY KEY,
		`p_body` TEXT DEFAULT NULL,
		`p_comments` INT DEFAULT 0,
		`p_date` INT NOT NULL DEFAULT 0,
		`p_ip` VARBINARY(16) DEFAULT NULL,
		`p_likes` INT DEFAULT 0,
		`p_nick` VARCHAR(24) NOT NULL DEFAULT '',
		`p_type` TINYINT DEFAULT 0,
		`p_update` INT NOT NULL DEFAULT 0,
		`p_user_pub` INT DEFAULT NULL,
		`p_user` INT DEFAULT NULL,
		/* Posibilidad de usarlo */
		`p_visibility` ENUM('everyone','followers','friends','nobody') DEFAULT 'everyone',
		`p_adult` TINYINT DEFAULT 0,
		INDEX idx_user (p_user),
		INDEX idx_user_pub (p_user_pub),
		INDEX idx_date (p_date)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
