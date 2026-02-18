<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

function json_response(int $code, array $data): void {
  http_response_code($code);
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  exit;
}

try {
  $user = current_user();
  if (!$user) json_response(401, ["error" => "Non connecté"]);

  $raw = file_get_contents('php://input');
  $data = [];
  if (!empty($raw) && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
    $data = json_decode($raw, true) ?? [];
  } else {
    $data = $_POST;
  }

  $menu_id         = (int)($data['menu_id'] ?? 0);
  $nb_personnes    = (int)($data['nb_personnes'] ?? 0);
  $date_prestation = trim((string)($data['date_prestation'] ?? ''));
  $heure_livraison = trim((string)($data['heure_livraison'] ?? ''));
  $adresse         = trim((string)($data['adresse_livraison'] ?? ''));
  $ville           = trim((string)($data['ville_livraison'] ?? ''));
  $km              = (float)($data['km'] ?? $data['km_hors_bordeaux'] ?? 0);

  if ($menu_id <= 0) json_response(400, ["error" => "menu_id manquant"]);
  if ($nb_personnes <= 0) json_response(400, ["error" => "nb_personnes invalide"]);
  if ($date_prestation === '') json_response(400, ["error" => "date_prestation manquante"]);
  if ($heure_livraison === '') json_response(400, ["error" => "heure_livraison manquante"]);
  if ($adresse === '') json_response(400, ["error" => "adresse_livraison manquante"]);
  if ($ville === '') json_response(400, ["error" => "ville_livraison manquante"]);

  $pdo = db();

  // menu + règles
  $stmt = $pdo->prepare("SELECT id, prix_min, nb_personnes_min, stock FROM menus WHERE id = ?");
  $stmt->execute([$menu_id]);
  $menu = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$menu) json_response(404, ["error" => "Menu introuvable"]);

  if ($nb_personnes < (int)$menu['nb_personnes_min']) {
    json_response(400, ["error" => "Nb personnes trop faible (min={$menu['nb_personnes_min']})"]);
  }
  if ((int)$menu['stock'] <= 0) json_response(400, ["error" => "Stock épuisé"]);

  $prix_menu = (float)$menu['prix_min'];
  $is_bordeaux = (mb_strtolower($ville) === 'bordeaux');

  // livraison / remise / total selon ton énoncé
  $prix_livraison = $is_bordeaux ? 0.0 : (5.0 + 0.59 * max(0.0, $km));
  $remise = ($nb_personnes >= 5) ? (0.10 * $prix_menu) : 0.0;
  $prix_total = $prix_menu + $prix_livraison - $remise;

  // Insert EXACTEMENT selon ta table
  $sql = "INSERT INTO commandes
    (user_id, menu_id, nb_personnes, date_prestation, heure_livraison, adresse_livraison, ville_livraison,
     km_hors_bordeaux, prix_menu, remise, prix_livraison, prix_total)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

  $pdo->prepare($sql)->execute([
    (int)$user['id'],
    $menu_id,
    $nb_personnes,
    $date_prestation,
    $heure_livraison,
    $adresse,
    $ville,
    $is_bordeaux ? 0.0 : $km,
    $prix_menu,
    $remise,
    $prix_livraison,
    $prix_total
  ]);

  $commande_id = (int)$pdo->lastInsertId();

  json_response(201, [
    "message" => "Commande créée",
    "commande_id" => $commande_id,
    "resume" => [
      "prix_menu" => round($prix_menu, 2),
      "prix_livraison" => round($prix_livraison, 2),
      "remise" => round($remise, 2),
      "prix_total" => round($prix_total, 2),
      "km_hors_bordeaux" => round($is_bordeaux ? 0.0 : $km, 2)
    ]
  ]);

} catch (Throwable $e) {
  json_response(500, ["error" => "Erreur serveur", "details" => $e->getMessage()]);
}
