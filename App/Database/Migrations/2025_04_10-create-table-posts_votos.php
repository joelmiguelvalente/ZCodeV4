<?php

# Migración para la tabla `posts_votos`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}posts_votos` (
		`voto_id` INT AUTO_INCREMENT PRIMARY KEY,
		`cant` INT NOT NULL DEFAULT 0,
		`date` INT NOT NULL DEFAULT 0,
		`tid` INT NOT NULL,
		`tuser` INT NOT NULL,
		`type` TINYINT(1) NOT NULL DEFAULT 1,
		INDEX idx_post (tid),
		INDEX idx_user (tuser)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
