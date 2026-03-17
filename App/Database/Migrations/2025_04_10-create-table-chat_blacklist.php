<?php

# Migración para la tabla `chat_blacklist`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}chat_blacklist` (
		`chat_ban_id` INT AUTO_INCREMENT PRIMARY KEY,
		`chat_ban_user` INT NOT NULL,
		`chat_ban_date` INT NOT NULL DEFAULT 0,
		`chat_ban_expire` INT NOT NULL DEFAULT 0,
		UNIQUE KEY unique_chat_ban (chat_ban_user, chat_ban_date),
		INDEX idx_user (chat_ban_user),
		INDEX idx_expire (chat_ban_expire)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
