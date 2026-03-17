<?php

# Migración para la tabla `comentarios_reaccion`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}comentarios_reaccion` (
		`rid` INT AUTO_INCREMENT PRIMARY KEY,
		`r_comment_id` INT NOT NULL, 
		`r_user_id` INT NOT NULL,
		`r_reaction` VARCHAR(20) NOT NULL DEFAULT '',
		`r_date` INT NOT NULL DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
