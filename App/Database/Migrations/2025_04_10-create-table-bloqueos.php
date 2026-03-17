<?php

# Migración para la tabla `bloqueos`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}bloqueos` (
		bid INT AUTO_INCREMENT PRIMARY KEY,
		b_user INT NOT NULL,
		b_auser INT NOT NULL,
		b_date INT NOT NULL DEFAULT 0,
		UNIQUE KEY unique_lock (b_user, b_auser),
		INDEX idx_user (b_user),
		INDEX idx_auser (b_auser) 
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
