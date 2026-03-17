<?php

# Migración para la tabla `fotos_favoritos`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}fotos_favoritos` (
		`fid` INT AUTO_INCREMENT PRIMARY KEY,
		`f_foto_id` INT DEFAULT 0,
		`f_user` INT DEFAULT 0,
		`f_date` INT NOT NULL DEFAULT 0,
		INDEX (f_foto_id),
		INDEX (f_user)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
