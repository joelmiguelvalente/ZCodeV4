<?php

# Migración para la tabla `fotos_votos`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}fotos_votos` (
		`vid` INT AUTO_INCREMENT PRIMARY KEY,
		`v_foto_id` INT DEFAULT 0,
		`v_user` INT DEFAULT 0,
		`v_pos` BIGINT NOT NULL DEFAULT 0,
		`v_neg` BIGINT NOT NULL DEFAULT 0,
		`v_date` INT NOT NULL DEFAULT 0,
		INDEX (v_foto_id),
		INDEX (v_user)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
