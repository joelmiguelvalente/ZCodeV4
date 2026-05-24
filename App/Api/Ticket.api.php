<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use App\Models\Ticket;

// NIVELES DE ACCESO Y PLANTILLAS DE CADA ACCI�N
$files = [
    'ticket-last-open' => ['n' => 4, 'p' => 'last-open'],
    'ticket-list' => ['n' => 4, 'p' => 'list'],
];

// REDEFINIR VARIABLES
$tsPage = 'php_files/p.ticket.' . $files[$action]['p'];

$tsLevel = $files[$action]['n'];

$tsAjax = empty($files[$action]['p']) ? 1 : 0;

// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);

if (!$tsLevelMsg) {
    echo '0: ' . $tsLevelMsg['mensaje'];
    die();
}
//
$do = $_GET['do'] ?? '';

// CLASE
$tsTicket = new Ticket();

// CODIGO
switch ($action) {
    case 'ticket-last-open':
        $smarty->assign("tsTicketOpen", $tsTicket->getTicketsOpenHome());
        break;
    case 'ticket-list':
        $getTickets = $tsTicket->getTickets();

        $smarty->assign('tsTicketList', $getTickets['data']);
        $smarty->assign('tsTicketPages', $getTickets['pages']);

        break;
}
