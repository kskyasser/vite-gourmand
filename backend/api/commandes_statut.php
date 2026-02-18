<?php
require __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

function json_response(int $code, array $data): void {
  http_response_code($code);
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  exit;
}

try {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();

  if (empty($_SESSION['user']) || empty($_SESSION['user']['id'])) {
    json_response(401, ["error" => "Non connecté"]);
  }

  // ✅ support GET, POST form, ou JSON
  $commande_id = 0;

  if (isset($_GET['commande_id'])) $commande_id = (int)$_GET['commande_id'];
  if (!$commande_id && isset($_GET['id'])) $commande_id = (int)$_GET['id'];

  if (!$commande_id && isset($_POST['commande_id'])) $commande_id = (int)$_POST['commande_id'];
  if (!$commande_id && isset($_POST['id'])) $commande_id = (int)$_POST['id'];

  if (!$commande_id) {
    $raw = file_get_contents('php://input');
    if ($raw) {
      $j = json_decode($raw, true);
      if (is_array($j)) {
        if (!empty($j['commande_id'])) $commande_id = (int)$j['commande_id'];
        if (!$commande_id && !empty($j['id'])) $commande_id = (int)$j['id'];
      }
    }
  }

  if ($commande_id <= 0) {
    json_response(400, ["error" => "commande_id manquant"]);
  }

  $pdo = db();

  // sécurité : user normal ne voit que ses commandes
  $userId = (int)$_SESSION['user']['id'];
  $role   = $_SESSION['user']['role'] ?? 'utilisateur';

  if (!in_array($role, ['employe','admin'], true)) {
    $check = $pdo->prepare("SELECT user_id FROM commandes WHERE id = ?");
    $check->execute([$commande_id]);
    $owner = $check->fetch(PDO::FETCH_ASSOC);
    if (!$owner) json_response(404, ["error" => "Commande introuvable"]);
    if ((int)$owner['user_id'] !== $userId) json_response(403, ["error" => "Accès interdit"]);
  }

  // ✅ table: commandes_statuts (comme chez toi)
  $stmt = $pdo->prepare("
    SELECT statut, created_at
    FROM commandes_statuts
    WHERE commande_id = ?
    ORDER BY created_at DESC, id DESC
    LIMIT 1
  ");
  $stmt->execute([$commande_id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  json_response(200, [
    "commande_id" => $commande_id,
    "statut" => $row['statut'] ?? null,
    "date" => $row['created_at'] ?? null
  ]);

} catch (Throwable $e) {
  json_response(500, ["error" => "Erreur serveur", "details" => $e->getMessage()]);
}
