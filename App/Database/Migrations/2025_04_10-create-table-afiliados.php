<?php

# Migración para la tabla `afiliados`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}afiliados` (
		aid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		a_titulo VARCHAR(80) NOT NULL,
		a_url VARCHAR(255) NOT NULL,
		a_banner VARCHAR(255) DEFAULT NULL,
		a_descripcion VARCHAR(255) DEFAULT NULL,
		a_sid VARCHAR(32) DEFAULT NULL,
		a_hits_in INT UNSIGNED DEFAULT 0,
		a_hits_out INT UNSIGNED DEFAULT 0,
		a_date INT NOT NULL DEFAULT 0,
		a_active TINYINT DEFAULT 1,
		INDEX idx_active (a_active),
		INDEX idx_code (a_sid)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
