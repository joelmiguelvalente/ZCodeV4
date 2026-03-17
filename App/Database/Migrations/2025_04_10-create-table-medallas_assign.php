<?php

# Migración para la tabla `medallas_assign`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}medallas_assign` (
		`id` INT AUTO_INCREMENT PRIMARY KEY,
		`medal_id` INT NOT NULL,
		`medal_for` INT NOT NULL,
		`medal_date` INT NOT NULL DEFAULT 0,
		`medal_ip` VARBINARY(16) DEFAULT NULL,
		UNIQUE KEY unique_award (medal_id, medal_for)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
