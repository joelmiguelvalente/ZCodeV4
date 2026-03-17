<?php

# Seed de datos para la tabla `tickets_type`

return [
    "INSERT INTO `{$prefix}tickets_type` (`type_id`, `type_title`, `type_icon`) VALUES
	(null, 'Avatar', 'face_happy'),
	(null, 'Buscador', 'search'),
	(null, 'Comentarios', 'thread'),
	(null, 'Cuenta', 'window_content'),
	(null, 'Fotos', 'camera_alt'),
	(null, 'Otro', 'frame'),
	(null, 'Perfil', 'fingerprint'),
	(null, 'Portal', 'directions'),
	(null, 'Posts', 'browser');"
];
