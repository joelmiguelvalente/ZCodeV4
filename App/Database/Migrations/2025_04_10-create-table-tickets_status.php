<?php

# Migración para la tabla `tickets_status`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}tickets_status` (
		`status_id` INT AUTO_INCREMENT PRIMARY KEY,
		`status_title` CHAR(30) NOT NULL DEFAULT '',
		`status_slug` CHAR(30) NOT NULL DEFAULT '',
		`status_icon` CHAR(20) NOT NULL DEFAULT ''
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
