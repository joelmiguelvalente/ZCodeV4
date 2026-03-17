<?php

# Migración para la tabla `tickets`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}tickets` (
		`ticket_id` INT AUTO_INCREMENT PRIMARY KEY,
		`ticket_user` INT DEFAULT 0,
		`ticket_title` VARCHAR(80) NOT NULL DEFAULT '',
		`ticket_body` TEXT DEFAULT NULL,
		`ticket_type` TINYINT NOT NULL DEFAULT 0,
		`ticket_status` TINYINT NOT NULL DEFAULT 0,
		`ticket_date` INT NOT NULL DEFAULT 0,
		`ticket_updated` INT NOT NULL DEFAULT 0,
		FULLTEXT (`ticket_title`, `ticket_body`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;"
];
