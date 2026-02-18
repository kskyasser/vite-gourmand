<?php
require __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = db(); // ✅ Initialisation PDO

    $stmt = $pdo->query("SELECT * FROM horaires ORDER BY id ASC");
    $horaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($horaires, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Erreur serveur",
        "details" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
