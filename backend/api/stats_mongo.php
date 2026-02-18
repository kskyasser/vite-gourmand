<?php
require __DIR__ . '/../config/mongo.php';
require_once __DIR__ . '/../utils/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  json_response(405, ['error' => 'Méthode non autorisée']);
}

session_start();

if (!isset($_SESSION['user'])) {
  json_response(401, ['error' => 'Non authentifié']);
}

$role = $_SESSION['user']['role'] ?? '';
if (!in_array($role, ['employe', 'admin'], true)) {
  json_response(403, ['error' => 'Accès refusé']);
}

// Filtres optionnels par date (format YYYY-MM-DD)
$start = $_GET['start'] ?? null;
$end   = $_GET['end'] ?? null;

// On ne garde que les vraies commandes archivées (pas le doc test:true)
$match = [
  'terminee_at' => ['$exists' => true],
  'commande_id' => ['$exists' => true],
  'prix_total'  => ['$exists' => true],
  'menu_id'     => ['$exists' => true],
  'menu_titre'  => ['$exists' => true],
];
function date_to_utc_ms(string $date, string $time): int {
  // On force en UTC
  $dt = new DateTimeImmutable($date . ' ' . $time, new DateTimeZone('UTC'));
  return (int)($dt->getTimestamp() * 1000);
}

if ($start) {
  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) {
    json_response(400, ['error' => 'Paramètre start invalide (YYYY-MM-DD)']);
  }
  $match['terminee_at']['$gte'] = new MongoDB\BSON\UTCDateTime(date_to_utc_ms($start, '00:00:00'));
}

if ($end) {
  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
    json_response(400, ['error' => 'Paramètre end invalide (YYYY-MM-DD)']);
  }
  // inclusif: on va jusqu'à 23:59:59
  $match['terminee_at']['$lte'] = new MongoDB\BSON\UTCDateTime(date_to_utc_ms($end, '23:59:59'));
}

try {
  $collection = mongo_db()->selectCollection('commandes');

  $pipeline = [];

  if (!empty($match)) {
    $pipeline[] = ['$match' => $match];
  }

  $pipeline[] = [
    '$group' => [
      '_id' => [
        'menu_id' => '$menu_id',
        'menu_titre' => '$menu_titre'
      ],
      'ca_total' => ['$sum' => '$prix_total'],
      'nb_commandes' => ['$sum' => 1]
    ]
  ];

  $pipeline[] = [
    '$project' => [
      '_id' => 0,
      'menu_id' => '$_id.menu_id',
      'menu_titre' => '$_id.menu_titre',
      'ca_total' => 1,
      'nb_commandes' => 1
    ]
  ];

  $pipeline[] = ['$sort' => ['ca_total' => -1]];

  $rows = iterator_to_array($collection->aggregate($pipeline), false);

  $total_ca = 0.0;
  foreach ($rows as $r) {
    $total_ca += (float)($r['ca_total'] ?? 0);
  }

  json_response(200, [
    'period' => ['start' => $start, 'end' => $end],
    'total_ca' => $total_ca,
    'items' => $rows
  ]);

} catch (Throwable $e) {
  json_response(500, ['error' => 'Erreur serveur', 'details' => $e->getMessage()]);
}
