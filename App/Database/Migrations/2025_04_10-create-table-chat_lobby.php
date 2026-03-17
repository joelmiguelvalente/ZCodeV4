<?php

# Migración para la tabla `chat_lobby`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}chat_lobby` (
		`lid` INT AUTO_INCREMENT PRIMARY KEY,
		`lobby_title` VARCHAR(32) NOT NULL DEFAULT '',
		`lobby_author` INT NOT NULL,
		`lobby_description` VARCHAR(150) NOT NULL DEFAULT '',
		`lobby_private` TINYINT NOT NULL DEFAULT 0,
		`lobby_guests` TEXT NULL,
		`lobby_date` INT NOT NULL DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
