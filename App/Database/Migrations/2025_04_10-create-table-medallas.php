<?php

# Migración para la tabla `medallas`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}medallas` (
		`medal_id` INT AUTO_INCREMENT PRIMARY KEY,
		`m_autor` INT NOT NULL,
		`m_cant` INT NOT NULL DEFAULT 0,
		`m_cond_foto` INT DEFAULT 0,
		`m_cond_post` INT DEFAULT 0,
		`m_cond_user` INT DEFAULT 0,
		`m_cond_user_rango` INT DEFAULT 0,
		`m_cond_comunidad` INT DEFAULT 0,
		`m_cond_video` INT DEFAULT 0,
		`m_date` INT NOT NULL DEFAULT 0,
		`m_description` VARCHAR(255) NOT NULL DEFAULT '',
		`m_image` VARCHAR(150) NOT NULL DEFAULT '',
		`m_title` VARCHAR(50) NOT NULL DEFAULT '',
		`m_total` INT NOT NULL DEFAULT 0,
		`m_type` TINYINT NOT NULL DEFAULT 0
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
