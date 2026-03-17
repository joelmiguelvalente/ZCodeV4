<?php

# Migración para la tabla `respuestas`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}respuestas` (
		`mr_id` INT AUTO_INCREMENT PRIMARY KEY,
		`mp_id` INT NOT NULL,
		`mr_from` INT NOT NULL,
		`mr_body` TEXT DEFAULT NULL,
		`mr_ip` VARBINARY(16) DEFAULT NULL,
		`mr_date` INT NOT NULL DEFAULT 0,
		INDEX idx_mp_id (mp_id),
		INDEX idx_mr_from (mr_from)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
