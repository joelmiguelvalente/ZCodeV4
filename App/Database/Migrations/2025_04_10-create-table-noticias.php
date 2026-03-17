<?php

# Migración para la tabla `noticias`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}noticias` (
		`not_id` INT AUTO_INCREMENT PRIMARY KEY,
		`not_title` VARCHAR(100) NOT NULL DEFAULT '',
		`not_body` TEXT NULL,
		`not_autor` INT DEFAULT 0,
		`not_date` INT NOT NULL DEFAULT 0,
		`not_expires` INT NOT NULL DEFAULT 0,
		`not_type` TINYINT NOT NULL DEFAULT 0, # 0 Normal | 1 Importante | 2 Cambios
		`not_color` ENUM('info','success','warning','danger','primary','secondary') DEFAULT 'info', 
		`not_active` TINYINT NOT NULL DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
