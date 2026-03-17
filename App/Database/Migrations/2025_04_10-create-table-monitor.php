<?php

# Migración para la tabla `monitor`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}monitor` (
		`not_id` INT AUTO_INCREMENT PRIMARY KEY,
		`not_date` INT NOT NULL DEFAULT 0,
		`not_menubar` TINYINT NOT NULL DEFAULT 2,
		`not_monitor` TINYINT NOT NULL DEFAULT 1,
		`not_total` TINYINT NOT NULL DEFAULT 1,
		`not_type` TINYINT NOT NULL DEFAULT 0,
		`obj_uno` INT NOT NULL DEFAULT 0,
		`obj_dos` INT NOT NULL DEFAULT 0,
		`obj_tres` INT NOT NULL DEFAULT 0,
		`obj_user` INT DEFAULT NULL,
		`user_id` INT DEFAULT NULL,
		INDEX idx_user (user_id),
		INDEX idx_type (not_type),
		INDEX idx_date (not_date)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
