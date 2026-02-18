<?php
require __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = db();

try {

  $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
  if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Paramètre id manquant ou invalide"]);
    exit;
  }

  // 1) Récupérer le menu
  
$stmt = $pdo->prepare("
  SELECT 
    m.*,
    (SELECT mi.url 
     FROM menu_images mi 
     WHERE mi.menu_id = m.id 
     ORDER BY mi.id ASC 
     LIMIT 1) AS image_url
  FROM menus m
  WHERE m.id = :id
  LIMIT 1
");

  $stmt->execute([':id' => $id]);
  $menu = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$menu) {
    http_response_code(404);
    echo json_encode(["error" => "Menu introuvable"]);
    exit;
  }

  // 2) Récupérer les plats liés au menu via menu_plat

  $stmt2 = $pdo->prepare("
    SELECT 
      p.id,
      p.type,
      p.nom,
      p.description,
      GROUP_CONCAT(a.nom ORDER BY a.nom SEPARATOR ', ') AS allergenes
    FROM menu_plat mp
    JOIN plats p ON p.id = mp.plat_id
    LEFT JOIN plat_allergene pa ON pa.plat_id = p.id
    LEFT JOIN allergenes a ON a.id = pa.allergene_id
    WHERE mp.menu_id = :id
    GROUP BY p.id, p.type, p.nom, p.description
    ORDER BY FIELD(p.type, 'entree','plat','dessert'), p.nom
  ");

  $stmt2->execute([':id' => $id]);
  $rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

  // 3) Grouper par type
  $plats = [
    "entree" => [],
    "plat" => [],
    "dessert" => []
  ];

  foreach ($rows as $r) {
    $t = $r['type'];
    if (!isset($plats[$t])) $plats[$t] = [];
    $plats[$t][] = $r;
  }

  echo json_encode([
    "menu" => $menu,
    "plats" => $plats
  ]);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["error" => "Erreur serveur", "details" => $e->getMessage()]);
}
