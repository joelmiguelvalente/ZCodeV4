<?php

# Migración para la tabla `chat`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}chat` (
		`cid` INT AUTO_INCREMENT PRIMARY KEY,
		`chat_lobby` INT NOT NULL,
		`chat_user` INT NOT NULL,
		`chat_message` TEXT NULL,
		`chat_date` INT NOT NULL DEFAULT 0,
		`chat_ip` VARBINARY(16) DEFAULT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
