<?php

# Migración para la tabla `posts_favoritos`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}posts_favoritos` (
		`fav_id` INT AUTO_INCREMENT PRIMARY KEY,
		`fav_user` INT NOT NULL,
		`fav_post_id` INT NOT NULL,
		`fav_date` INT NOT NULL DEFAULT 0,
		INDEX idx_post (fav_post_id),
		INDEX idx_user (fav_user)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
