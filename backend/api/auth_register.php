<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/../lib/mailer.php';

header('Content-Type: application/json; charset=utf-8');

$body = $_POST;

$nom = trim($body['nom'] ?? '');
$prenom = trim($body['prenom'] ?? '');
$email = trim($body['email'] ?? '');
$gsm = trim($body['gsm'] ?? '');
$adresse = trim($body['adresse'] ?? '');
$password = $body['password'] ?? '';
// Validation mot de passe (ECF)
$pwd = (string)$password;
$okLen = strlen($pwd) >= 10;
$okUpper = preg_match('/[A-Z]/', $pwd);
$okLower = preg_match('/[a-z]/', $pwd);
$okDigit = preg_match('/[0-9]/', $pwd);
$okSpecial = preg_match('/[^A-Za-z0-9]/', $pwd);

if (!$okLen || !$okUpper || !$okLower || !$okDigit || !$okSpecial) {
    http_response_code(400);
    echo json_encode([
        "error" => "Mot de passe invalide : 10 caractères min, 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial."
    ]);
    exit;
}
if ($nom === '' || $prenom === '' || $email === '' || $gsm === '' || $adresse === '' || $password === '') {
    http_response_code(400);
    echo json_encode(["error" => "Tous les champs sont obligatoires"]);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Email invalide"]);
    exit;
}

try {
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        http_response_code(400);
        echo json_encode(["error" => "Email déjà utilisé"]);
        exit;
    }

    // rôle utilisateur = id 1
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO users (role_id, nom, prenom, email, gsm, adresse, password_hash)
        VALUES (1, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$nom, $prenom, $email, $gsm, $adresse, $hash]);
send_mail(
    $email,
    "Bienvenue sur Vite Gourmand",
    "<p>Bonjour <b>".htmlspecialchars($prenom)."</b>,</p>
     <p>Bienvenue sur Vite Gourmand ! Ton compte est bien créé.</p>
     <p>À bientôt 🍽️</p>"
);
    echo json_encode(["message" => "Inscription réussie"]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur serveur"]);
}
