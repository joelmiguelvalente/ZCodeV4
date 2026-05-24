<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

if (! defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

$files = [
    'github-api' => ['n' => 2, 'p' => ''],
];

// REDEFINIR VARIABLES
$tsPage = 'ajax/p.github.' . $files[$action]['p'];
$tsLevel = $files[$action]['n'];
$tsAjax = empty($files[$action]['p']) ? 1 : 0;

// DEPENDE EL NIVEL
$tsLevelMsg = $tsCore->setLevel($tsLevel, true);
if ($tsLevelMsg != 1) :
    echo '0: ' . $tsLevelMsg['mensaje'];
    die();
endif;

// CODIGO
switch ($action) {
    case 'github-api':
      $branch = rawurlencode(trim($_GET['branch'] ?? 'dev'));
      $url    = "https://api.github.com/repos/joelmiguelvalente/ZCodeV4/commits/{$branch}";

      $context = stream_context_create([
         'http' => [
            'method' => 'GET',
            'header' => [
               'User-Agent: ZCodeApp',
               'Accept: application/vnd.github.v3+json',
            ],
            'timeout' => 5,
         ]
      ]);

      $response = @file_get_contents($url, false, $context);

      if ($response === false) {
         echo json_encode(['state' => 0, 'data' => 'No se pudo conectar con la API de GitHub']);
         die();
      }

      $data = json_decode($response, true);

      if (empty($data) || isset($data['message'])) {
         echo json_encode(['state' => 0, 'data' => 'Rama no encontrada o sin commits']);
         die();
      }

      echo json_encode([
         'state' => 1,
         'data'  => [
            'sha'      => $data['sha'],
            'html_url' => $data['html_url'],
            'author'   => $data['commit']['author']['name'],
            'message'  => $data['commit']['message'],
            'date'     => $data['commit']['author']['date'],
            'verified' => $data['commit']['verification']['verified'],
            'reason'   => $data['commit']['verification']['reason'],
         ]
      ]);
   break;
}
