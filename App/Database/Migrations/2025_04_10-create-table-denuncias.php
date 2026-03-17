<?php

# Migración para la tabla `denuncias`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}denuncias` (
		`did` INT AUTO_INCREMENT PRIMARY KEY,
		`d_date` INT NOT NULL DEFAULT 0,
		`d_extra` TEXT DEFAULT NULL,
		`d_razon` TINYINT NOT NULL,
		`d_total` SMALLINT NOT NULL DEFAULT 1,
		`d_type` TINYINT NOT NULL DEFAULT 0,
		`d_user` INT NOT NULL,
		`obj_id` INT NOT NULL,
		INDEX idx_type (d_type),
		INDEX idx_obj (obj_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
