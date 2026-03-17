<?php

# Migración para la tabla `posts`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}posts` (
		`post_id` INT AUTO_INCREMENT PRIMARY KEY,
		`post_category` INT DEFAULT 0,
		`post_title` VARCHAR(120) DEFAULT '',
		`post_body` TEXT NULL,
		`post_user` INT DEFAULT 0,
		`post_cache` INT DEFAULT 0,
		`post_comments` BIGINT DEFAULT 0,
		`post_collection` INT DEFAULT 0,
		`post_favoritos` INT NOT NULL DEFAULT 0,
		`post_hits` INT NOT NULL DEFAULT 0,
		`post_portada` VARCHAR(255) NOT NULL DEFAULT '',
		`post_private` TINYINT NOT NULL DEFAULT 0,
		`post_puntos` BIGINT UNSIGNED NOT NULL DEFAULT 0,
		`post_seguidores` BIGINT NOT NULL DEFAULT 0,
		`post_shared` BIGINT NOT NULL DEFAULT 0,
		`post_smileys` TINYINT NOT NULL DEFAULT 0,
		`post_sponsored` TINYINT NOT NULL DEFAULT 0,
		`post_draft` TINYINT NOT NULL DEFAULT 0,
		`post_status` TINYINT NOT NULL DEFAULT 0,
		`post_sticky` TINYINT NOT NULL DEFAULT 0,
		`post_tags` VARCHAR(128) NOT NULL DEFAULT '',
		`post_fuentes` TEXT NULL,
		`post_date` INT NOT NULL DEFAULT 0,
		`post_update` INT NOT NULL DEFAULT 0,
		`post_block_comments` TINYINT NOT NULL DEFAULT 0,
		`post_visitantes` TINYINT NOT NULL DEFAULT 0,
		`post_ip` VARBINARY(16) DEFAULT NULL,
		FULLTEXT INDEX ft_index (post_tags),
		INDEX idx_category (post_category),
		INDEX idx_user (post_user),
		INDEX idx_status (post_status),
		INDEX idx_draft (post_draft)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;"
];
