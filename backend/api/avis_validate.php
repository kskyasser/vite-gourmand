<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/auth.php';
require_role(['employe','admin']);

header('Content-Type: application/json; charset=utf-8');

function json_response(int $code, array $data): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$raw = file_get_contents('php://input');
$body = [];
if (!empty($raw) && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
    $body = json_decode($raw, true) ?? [];
} else {
    $body = $_POST;
}

$avis_id = isset($body['avis_id']) ? (int)$body['avis_id'] : 0;
$is_validated = isset($body['is_validated']) ? (int)$body['is_validated'] : 1; // 1 = valider, 0 = refuser

if ($avis_id <= 0) {
    json_response(400, ["error" => "Champ obligatoire: avis_id"]);
}
if ($is_validated !== 0 && $is_validated !== 1) {
    json_response(400, ["error" => "is_validated doit être 0 ou 1"]);
}

try {
    $check = $pdo->prepare("SELECT id, is_validated FROM avis WHERE id = ?");
    $check->execute([$avis_id]);
    $avis = $check->fetch();

    if (!$avis) {
        json_response(404, ["error" => "Avis introuvable"]);
    }

    $upd = $pdo->prepare("UPDATE avis SET is_validated = ? WHERE id = ?");
    $upd->execute([$is_validated, $avis_id]);

    json_response(200, [
        "message" => $is_validated ? "Avis validé" : "Avis refusé",
        "avis_id" => $avis_id,
        "is_validated" => $is_validated
    ]);
} catch (Throwable $e) {
    json_response(500, ["error" => "Erreur serveur", "details" => $e->getMessage()]);
}
