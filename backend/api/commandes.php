<?php
require __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = db(); // ✅ IMPORTANT (sinon $pdo undefined = 500)

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
$statut  = isset($_GET['statut']) ? trim($_GET['statut']) : null;

try {
    $params = [];
    $where  = [];

    if ($user_id) {
        $where[]  = 'c.user_id = ?';
        $params[] = $user_id;
    }

    if ($statut) {
        $where[]  = 'cs.statut = ?';
        $params[] = $statut;
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "
        SELECT
            c.id,
            c.user_id,
            u.email AS user_email,
            c.menu_id,
            m.titre AS menu_titre,
            c.nb_personnes,
            c.ville_livraison,
            c.km_hors_bordeaux,
            c.prix_menu,
            c.remise,
            c.prix_livraison,
            c.prix_total,
            c.created_at,
            cs.statut AS dernier_statut,
            cs.created_at AS statut_date
        FROM commandes c
        JOIN users u ON u.id = c.user_id
        JOIN menus m ON m.id = c.menu_id
        LEFT JOIN commandes_statuts cs
          ON cs.commande_id = c.id
         AND cs.created_at = (
            SELECT MAX(cs2.created_at)
            FROM commandes_statuts cs2
            WHERE cs2.commande_id = c.id
         )
        $whereSql
        ORDER BY c.id DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "error"   => "Erreur serveur",
        "details" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
