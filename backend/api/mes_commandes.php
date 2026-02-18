<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['user']) || empty($_SESSION['user']['id'])) {
        http_response_code(401);
        echo json_encode(["error" => "Non connecté"], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    $userId = (int)$_SESSION['user']['id'];

    // ✅ PDO
    $pdo = db();

    $sql = "
        SELECT
            c.id,
            c.user_id,
            c.menu_id,
            m.titre AS menu_titre,
            c.nb_personnes,
            c.ville_livraison,
            c.prix_total,
            c.created_at
        FROM commandes c
        JOIN menus m ON m.id = c.menu_id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC, c.id DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Erreur serveur",
        "details" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
