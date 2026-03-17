<?php

# Migración para la tabla `fotos_comentarios`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}fotos_comentarios` (
		`cid` INT AUTO_INCREMENT PRIMARY KEY,
		`c_foto_id` INT DEFAULT 0,
		`c_user` INT DEFAULT 0,
		`c_date` INT NOT NULL DEFAULT 0,
		`c_update` INT NOT NULL DEFAULT 0,
		`c_body` TEXT NULL,
		`c_ip` VARBINARY(16) DEFAULT NULL,
		INDEX (c_foto_id),
		INDEX (c_user)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
