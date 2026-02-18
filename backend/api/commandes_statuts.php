<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // On exige une session + rôle autorisé
    require_role(['utilisateur', 'employe', 'admin']);

    // Récup user depuis la session (pas besoin de current_user())
    $user = $_SESSION['user'] ?? null;
    $role = $user['role'] ?? '';
    $user_id = (int)($user['id'] ?? 0);

    $commande_id = isset($_GET['commande_id']) ? (int)$_GET['commande_id'] : 0;
    if ($commande_id <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "commande_id manquant"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo = db();

    // Sécurité: si c'est un utilisateur, il ne voit que ses commandes
    if ($role === 'utilisateur') {
        $check = $pdo->prepare("SELECT id FROM commandes WHERE id = ? AND user_id = ?");
        $check->execute([$commande_id, $user_id]);
        if (!$check->fetch()) {
            http_response_code(403);
            echo json_encode(["error" => "Accès refusé"], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // Dernier statut
    $q = $pdo->prepare("
        SELECT statut, created_at
        FROM commandes_statuts
        WHERE commande_id = ?
        ORDER BY id DESC
        LIMIT 1
    ");
    $q->execute([$commande_id]);
    $row = $q->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode([
            "commande_id" => $commande_id,
            "statut" => "en_attente",
            "date" => null
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    echo json_encode([
        "commande_id" => $commande_id,
        "statut" => $row["statut"],
        "date" => $row["created_at"]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Erreur serveur",
        "details" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
