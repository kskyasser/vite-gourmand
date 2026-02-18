<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

$body = $_POST;
if (empty($body)) {
  $raw = file_get_contents('php://input');
  $json = json_decode($raw, true);
  if (is_array($json)) $body = $json;
}

$email = trim($body['email'] ?? '');
$token = (string)($body['token'] ?? '');
$newPassword = (string)($body['password'] ?? '');

if ($email === '' || $token === '' || $newPassword === '') {
  http_response_code(400);
  echo json_encode(["error" => "Champs manquants"]);
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(["error" => "Email invalide"]);
  exit;
}

// Validation mot de passe (ECF)
$pwd = $newPassword;
$okLen = strlen($pwd) >= 10;
$okUpper = preg_match('/[A-Z]/', $pwd);
$okLower = preg_match('/[a-z]/', $pwd);
$okDigit = preg_match('/[0-9]/', $pwd);
$okSpecial = preg_match('/[^A-Za-z0-9]/', $pwd);

if (!$okLen || !$okUpper || !$okLower || !$okDigit || !$okSpecial) {
  http_response_code(400);
  echo json_encode(["error" => "Mot de passe invalide : règle ECF."]);
  exit;
}

try {
  $u = $pdo->prepare("SELECT id FROM users WHERE email = ?");
  $u->execute([$email]);
  $user = $u->fetch(PDO::FETCH_ASSOC);

  if (!$user) {
    http_response_code(400);
    echo json_encode(["error" => "Token invalide"]);
    exit;
  }

  $stmt = $pdo->prepare("
    SELECT id, token_hash, expires_at, used_at
    FROM password_resets
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 1
  ");
  $stmt->execute([(int)$user['id']]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row || $row['used_at'] !== null) {
    http_response_code(400);
    echo json_encode(["error" => "Token invalide"]);
    exit;
  }

  if (new DateTime($row['expires_at']) < new DateTime()) {
    http_response_code(400);
    echo json_encode(["error" => "Token expiré"]);
    exit;
  }

  if (!password_verify($token, $row['token_hash'])) {
    http_response_code(400);
    echo json_encode(["error" => "Token invalide"]);
    exit;
  }

  $hash = password_hash($newPassword, PASSWORD_BCRYPT);
  $up = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
  $up->execute([$hash, (int)$user['id']]);

  $mark = $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = ?");
  $mark->execute([(int)$row['id']]);

  echo json_encode(["message" => "Mot de passe mis à jour"]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["error" => "Erreur serveur"]);
}
