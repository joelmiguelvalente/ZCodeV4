<?php

# Migración para la tabla `posts_tags`

return [
    "CREATE TABLE IF NOT EXISTS `{$prefix}posts_tags` (
      post_id INT NOT NULL,
      tag VARCHAR(50) NOT NULL,
      PRIMARY KEY(post_id, tag)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];
