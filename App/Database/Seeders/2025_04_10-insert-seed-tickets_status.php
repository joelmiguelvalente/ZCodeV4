<?php

# Seed de datos para la tabla `tickets_status`

return [
    "INSERT INTO `{$prefix}tickets_status` (`status_id`, `status_title`, `status_slug`, `status_icon`) VALUES
	(null, 'En espera', 'en-espera', 'clock'),
	(null, 'En proceso', 'en-proceso', 'loader'),
	(null, 'Finalizado', 'finalizado', 'check'),
	(null, 'Abandonado', 'abandonado', 'no_sign'),
	(null, 'Pausado', 'pausado', 'refresh'),
	(null, 'Cancelado', 'cancelado', 'close');"
];
