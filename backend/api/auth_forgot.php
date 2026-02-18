<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/../lib/mailer.php';

header('Content-Type: application/json; charset=utf-8');

$body = $_POST;
if (empty($body)) {
  $raw = file_get_contents('php://input');
  $json = json_decode($raw, true);
  if (is_array($json)) $body = $json;
}

$email = trim($body['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(["error" => "Email invalide"]);
  exit;
}

// Réponse neutre (sécurité)
$neutral = ["message" => "Si un compte existe, un email a été envoyé."];

try {
  $stmt = $pdo->prepare("SELECT id, prenom FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user) {
    echo json_encode($neutral);
    exit;
  }

  $token = bin2hex(random_bytes(32));
  $tokenHash = password_hash($token, PASSWORD_BCRYPT);
  $expires = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s');

  $ins = $pdo->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
  $ins->execute([(int)$user['id'], $tokenHash, $expires]);

  // ⚠️ Mets ici le port Vite actuel (chez toi: 5173)
  $resetLink = "http://localhost:5173/#/reset?token=" . urlencode($token) . "&email=" . urlencode($email);

  send_mail(
    $email,
    "Réinitialisation du mot de passe - Vite Gourmand",
    "<p>Bonjour <b>" . htmlspecialchars($user['prenom']) . "</b>,</p>
     <p>Pour réinitialiser ton mot de passe, clique ici :</p>
     <p><a href=\"" . $resetLink . "\">Réinitialiser mon mot de passe</a></p>
     <p>Ce lien expire dans 30 minutes.</p>"
  );

  echo json_encode($neutral);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["error" => "Erreur serveur"]);
}
