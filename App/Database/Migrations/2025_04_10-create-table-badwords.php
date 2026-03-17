<?php

# Migración para la tabla `badwords`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}badwords` (
		wid INT AUTO_INCREMENT PRIMARY KEY,
		word VARCHAR(255) DEFAULT NULL,
		swop VARCHAR(255) DEFAULT NULL,
		method TINYINT NOT NULL DEFAULT 0,
		type TINYINT NOT NULL DEFAULT 0,
		author INT DEFAULT NULL,
		reason VARCHAR(255) DEFAULT NULL,
		date INT NOT NULL DEFAULT 0,
		INDEX idx_word (word)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;"
];
