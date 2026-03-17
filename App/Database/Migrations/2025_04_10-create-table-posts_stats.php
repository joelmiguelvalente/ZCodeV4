<?php

# Migración para la tabla `posts_stats`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}posts_stats` (
		`sid` INT AUTO_INCREMENT PRIMARY KEY,
		`stats_in` CHAR(30) NOT NULL DEFAULT '', 
		`stats_user` INT DEFAULT 0,
		`stats_post_id` INT DEFAULT 0,
		`stats_date` INT NOT NULL DEFAULT 0,
		INDEX idx_post (stats_post_id),
		INDEX idx_user (stats_user)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
