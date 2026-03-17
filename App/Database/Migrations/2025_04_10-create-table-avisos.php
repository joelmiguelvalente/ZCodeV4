<?php

# Migración para la tabla `avisos`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}avisos` (
		av_id INT AUTO_INCREMENT PRIMARY KEY,
		user_id INT NOT NULL,
		av_subject VARCHAR(42) DEFAULT NULL,
		av_body TEXT DEFAULT NULL,
		av_date INT NOT NULL DEFAULT 0,
		av_read TINYINT NOT NULL DEFAULT 0,
		av_type TINYINT NOT NULL DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
