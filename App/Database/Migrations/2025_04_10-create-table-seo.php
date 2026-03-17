<?php

# Migración para la tabla `seo`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}seo` (
		`seo_id` int(11) PRIMARY KEY DEFAULT 0,
		`seo_titulo` VARCHAR(60) NOT NULL DEFAULT '',
		`seo_descripcion` VARCHAR(160) NOT NULL DEFAULT '',
		`seo_portada` VARCHAR(255) NOT NULL DEFAULT '',
		`seo_keywords` VARCHAR(255) NOT NULL DEFAULT '',
		`seo_robots` TINYINT NULL DEFAULT 0,
		`seo_robots_data` VARCHAR(200) NOT NULL DEFAULT '',
		`seo_sitemap` TINYINT NULL DEFAULT 0,
		`seo_google_verification_active` TINYINT NULL DEFAULT 0,
		`seo_google_verification` VARCHAR(60) NULL DEFAULT '',
		`seo_google_analytics` VARCHAR(20) NOT NULL DEFAULT ''
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;"
];
