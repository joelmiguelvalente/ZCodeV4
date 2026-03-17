<?php

# Migración para la tabla `muro_likes`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}muro_likes` (
		`like_id` INT AUTO_INCREMENT PRIMARY KEY,
		`user_id` INT DEFAULT NULL,
		`obj_id` INT DEFAULT NULL,
		`obj_type` TINYINT NOT NULL,
		INDEX idx_obj (obj_type, obj_id)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
