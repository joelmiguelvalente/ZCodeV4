<?php

# Migración para la tabla `blacklist`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}blacklist` (
		id INT AUTO_INCREMENT PRIMARY KEY,
		type TINYINT NOT NULL DEFAULT 0,
		value VARCHAR(100) NOT NULL,
		reason VARCHAR(255) DEFAULT NULL,
		author INT DEFAULT NULL,
		date INT NOT NULL DEFAULT 0,
		INDEX idx_value (value)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
