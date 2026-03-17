<?php

# Migración para la tabla `tickets_type`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}tickets_type` (
		`type_id` INT AUTO_INCREMENT PRIMARY KEY,
		`type_title` CHAR(30) NOT NULL DEFAULT '',
		`type_icon` CHAR(20) NOT NULL DEFAULT ''
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
