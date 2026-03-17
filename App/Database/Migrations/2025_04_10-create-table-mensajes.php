<?php

# Migración para la tabla `mensajes`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}mensajes` (
		`mp_id` INT AUTO_INCREMENT PRIMARY KEY,
		`mp_answer` TINYINT NOT NULL DEFAULT 0,
		`mp_date` INT NOT NULL DEFAULT 0,
		`mp_del_from` TINYINT NOT NULL DEFAULT 0,
		`mp_del_to` TINYINT NOT NULL DEFAULT 0,
		`mp_from` INT NOT NULL,
		`mp_preview` VARCHAR(100) DEFAULT NULL,
		`mp_read_from` TINYINT NOT NULL DEFAULT 1,
		`mp_read_mon_from` TINYINT NOT NULL DEFAULT 1,
		`mp_read_mon_to` TINYINT NOT NULL DEFAULT 0,
		`mp_read_to` TINYINT NOT NULL DEFAULT 0,
		`mp_subject` VARCHAR(100) DEFAULT NULL,
		`mp_to` INT NOT NULL,
		INDEX idx_to (mp_to),
		INDEX idx_from (mp_from),
		INDEX idx_read_to (mp_read_to),
		INDEX idx_read_from (mp_read_from)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
