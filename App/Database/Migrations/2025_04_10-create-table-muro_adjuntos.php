<?php

# Migración para la tabla `muro_adjuntos`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}muro_adjuntos` (
		`adj_id` INT AUTO_INCREMENT PRIMARY KEY,
		`adj_description` VARCHAR(255) NOT NULL DEFAULT '', # a_desc
		`adj_image` VARCHAR(255) NOT NULL DEFAULT '', # a_img
		`adj_title` VARCHAR(100) NOT NULL DEFAULT '',
		`adj_url` VARCHAR(255) NOT NULL DEFAULT '',
		`adj_date` VARCHAR(255) NOT NULL DEFAULT '',
		`pub_id` INT NOT NULL DEFAULT 0,
		INDEX idx_pub_id (pub_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
