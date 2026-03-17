<?php

# Migración para la tabla `posts_collections`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}posts_collections` (
		`col_id` INT AUTO_INCREMENT PRIMARY KEY,
		`col_title` VARCHAR(30) NOT NULL DEFAULT '',
		`col_cover` VARCHAR(255) NOT NULL DEFAULT '',
		`col_user` INT NOT NULL DEFAULT 0,
		`col_date` INT NOT NULL DEFAULT 0,
		INDEX idx_user (col_user)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
