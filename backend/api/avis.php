<?php
require __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$menu_id = isset($_GET['menu_id']) ? (int)$_GET['menu_id'] : 0;
$limit   = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

if ($limit <= 0) $limit = 10;
if ($limit > 50) $limit = 50;

try {
    $pdo = db(); // ✅ IMPORTANT : initialise PDO

    $params = [];
    $where = "WHERE a.is_validated = 1";

    if ($menu_id > 0) {
        $where .= " AND a.menu_id = ?";
        $params[] = $menu_id;
    }

    $sql = "
        SELECT
          a.id,
          a.menu_id,
          m.titre AS menu_titre,
          a.note,
          a.commentaire,
          a.created_at
        FROM avis a
        JOIN menus m ON m.id = a.menu_id
        $where
        ORDER BY a.created_at DESC
        LIMIT $limit
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Erreur serveur",
        "details" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
