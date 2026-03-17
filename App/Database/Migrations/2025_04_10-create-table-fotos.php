<?php

# Migración para la tabla `fotos`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}fotos` (
		`foto_id` INT AUTO_INCREMENT PRIMARY KEY,
		`f_album` INT DEFAULT 0,
		`f_title` VARCHAR(80) NOT NULL DEFAULT '',
		`f_date` INT NOT NULL DEFAULT 0,
		`f_update` INT NOT NULL DEFAULT 0,
		`f_description` TEXT DEFAULT NULL,
		`f_url` VARCHAR(255) NOT NULL DEFAULT '',
		`f_user` INT DEFAULT 0,
		`f_closed` TINYINT NOT NULL DEFAULT 0,
		`f_visitas` BIGINT DEFAULT 0,
		`f_status` TINYINT NOT NULL DEFAULT 0,
		`f_last` INT NOT NULL DEFAULT 0,
		`f_hits` BIGINT NOT NULL DEFAULT 0,
		`f_ip` VARBINARY(16) DEFAULT NULL,
		INDEX (f_user),
		INDEX (f_album),
		INDEX (f_date),
		INDEX (f_status)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
