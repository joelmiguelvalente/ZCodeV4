<?php

# Migración para la tabla `suspension`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}suspension` (
		`susp_id` INT AUTO_INCREMENT PRIMARY KEY,
		`user_id` INT DEFAULT 0,
		`susp_causa` TEXT DEFAULT NULL,
		`susp_date` INT NOT NULL DEFAULT 0,
		`susp_termina` INT NOT NULL DEFAULT 0,
		`susp_mod` INT DEFAULT 0,
		`susp_ip` VARBINARY(16) DEFAULT NULL,
		INDEX idx_user (user_id),
		INDEX idx_mod (susp_mod)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
