<?php

# Migración para la tabla `posts_comentarios`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}posts_comentarios` (
		`cid` INT AUTO_INCREMENT PRIMARY KEY,
		`c_post_id` INT NOT NULL,
		`c_user` INT NOT NULL,
		`c_date` INT NOT NULL DEFAULT 0,
		`c_update` INT NOT NULL DEFAULT 0,
		`c_body` TEXT NULL,
		`c_reaccion` ENUM('','like','love','haha','wow','sad','angry') NOT NULL DEFAULT '',
		`c_status` INT NOT NULL DEFAULT 0,
		`c_answer` INT NOT NULL DEFAULT 0,
		`c_answer_cid` INT NOT NULL DEFAULT 0,
		`c_ip` VARBINARY(16) DEFAULT NULL,
		INDEX idx_post (c_post_id),
		INDEX idx_user (c_user)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
