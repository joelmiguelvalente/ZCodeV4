<?php

# Migración para la tabla `portal`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}portal` (
		`user_id` INT PRIMARY KEY,
		`last_posts_visited` TEXT NULL,
		`last_posts_shared` TEXT NULL,
		`last_posts_cats` TEXT NULL,
		`c_monitor` VARCHAR(255) NOT NULL DEFAULT 'f1,f2,f3,f8,f9,f4,f5,f10,f6,f7,f11,f12,f13,f14,f18,f19,20,f21'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];
