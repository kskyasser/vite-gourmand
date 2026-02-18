<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(["error" => "Email et mot de passe obligatoires"], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // ✅ une seule connexion PDO
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT u.id, u.nom, u.prenom, u.email, r.name AS role, u.password_hash
        FROM users u
        JOIN roles r ON r.id = u.role_id
        WHERE u.email = ? AND u.is_active = 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(["error" => "Identifiants invalides"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    unset($user['password_hash']);
    $_SESSION['user'] = $user;

    echo json_encode([
        "message" => "Connexion réussie",
        "user" => $user
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Erreur serveur",
        "details" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
