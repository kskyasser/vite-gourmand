<?php
require __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
$pdo = db();
try {

$stmt = $pdo->query("
  SELECT 
    m.id, m.titre, m.description, m.theme, m.regime, m.nb_personnes_min, m.prix_min, m.stock,
    (SELECT mi.url FROM menu_images mi WHERE mi.menu_id = m.id ORDER BY mi.id ASC LIMIT 1) AS image_url
  FROM menus m
  ORDER BY m.id DESC
");
    $menus = $stmt->fetchAll();

    echo json_encode($menus, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Erreur serveur",
        "details" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
