<?php

# Migración para la tabla `follows`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}follows` (
		`follow_id` INT AUTO_INCREMENT PRIMARY KEY,
		`f_date` INT NOT NULL DEFAULT 0,
		`f_id` INT NOT NULL,
		`f_type` TINYINT NOT NULL DEFAULT 0,
		`f_user` INT NOT NULL,
		UNIQUE KEY unique_follow (f_user, f_id, f_type),
		INDEX idx_user (f_user),
		INDEX idx_target (f_id) 
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
