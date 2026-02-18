<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

function json_response(int $code, array $data): void {
  http_response_code($code);
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  exit;
}

try {
  // ✅ connecté (utilisateur / employe / admin)
  require_role(['utilisateur', 'employe', 'admin']);

  // ✅ user depuis la session (pas besoin de current_user())
  $user = $_SESSION['user'] ?? null;
  $userId = (int)($user['id'] ?? 0);
  if ($userId <= 0) json_response(401, ["error" => "Non connecté"]);

  // Lire body (form ou JSON)
  $raw = file_get_contents('php://input');
  $data = $_POST;

  if (empty($data) && $raw && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
    $j = json_decode($raw, true);
    if (is_array($j)) $data = $j;
  }

  $menu_id = isset($data['menu_id']) ? (int)$data['menu_id'] : 0;
  $note = isset($data['note']) ? (int)$data['note'] : 0;
  $commentaire = isset($data['commentaire']) ? trim((string)$data['commentaire']) : '';

  if ($menu_id <= 0) json_response(400, ["error" => "menu_id manquant"]);
  if ($note < 1 || $note > 5) json_response(400, ["error" => "Note invalide (1 à 5)"]);
  if (mb_strlen($commentaire) < 2) json_response(400, ["error" => "Commentaire trop court"]);

  $pdo = db();

  // ✅ Règle: avis seulement si commande terminée pour ce menu
$q = $pdo->prepare("
  SELECT 1
  FROM commandes c
  JOIN commandes_statuts cs ON cs.commande_id = c.id
  WHERE c.user_id = ?
    AND c.menu_id = ?
    AND cs.statut = 'terminee'
  ORDER BY cs.created_at DESC, cs.id DESC
  LIMIT 1
");
$q->execute([$userId, $menu_id]);

  if (!$q->fetchColumn()) {
    json_response(403, [
      "error" => "Avis interdit",
      "details" => "Avis possible uniquement après une commande terminée pour ce menu."
    ]);
  }

  // ✅ Insert avis (ta table a is_validated d'après tes SELECT)
  $ins = $pdo->prepare("
    INSERT INTO avis (user_id, menu_id, note, commentaire, is_validated, created_at)
    VALUES (?, ?, ?, ?, 0, NOW())
  ");
  $ins->execute([$userId, $menu_id, $note, $commentaire]);

  json_response(201, [
    "message" => "Avis enregistré (en attente de validation)",
    "avis_id" => (int)$pdo->lastInsertId()
  ]);

} catch (Throwable $e) {
  json_response(500, ["error" => "Erreur serveur", "details" => $e->getMessage()]);
}
