<?php

# Migración para la tabla `muro_comentarios`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}muro_comentarios` (
		`cid` INT AUTO_INCREMENT PRIMARY KEY,
		`pub_id` INT DEFAULT NULL,
		`c_user` INT DEFAULT NULL,
		`c_date` INT NOT NULL DEFAULT 0,
		`c_body` TEXT,
		`c_likes` INT DEFAULT 0,
		`c_ip` VARBINARY(16) DEFAULT NULL,
		INDEX idx_pub_id (pub_id),
		INDEX idx_c_user (c_user)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
