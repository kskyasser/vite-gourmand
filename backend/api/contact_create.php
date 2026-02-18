<?php
require __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

function json_response(int $code, array $data): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Accepte JSON ou form-urlencoded
$raw = file_get_contents('php://input');
$body = [];
if (!empty($raw) && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
    $body = json_decode($raw, true) ?? [];
} else {
    $body = $_POST;
}

$email = trim($body['email'] ?? '');
$titre = trim($body['titre'] ?? '');
$description = trim($body['description'] ?? '');

if ($email === '' || $titre === '' || $description === '') {
    json_response(400, ["error" => "Champs obligatoires: email, titre, description"]);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(400, ["error" => "Email invalide"]);
}
if (mb_strlen($titre) > 120) {
    json_response(400, ["error" => "Titre trop long (max 120)"]);
}

try {
    // 1) Stocker en base
    $stmt = $pdo->prepare("INSERT INTO contacts (email, titre, description) VALUES (?, ?, ?)");
    $stmt->execute([$email, $titre, $description]);

    $contact_id = (int)$pdo->lastInsertId();

    // 2) Envoi mail (local peut échouer -> fallback)
    $to = 'contact@vite-gourmand.test';
    $subject = "[Contact] " . $titre;
    $message = "Email: $email\n\nMessage:\n$description\n\nID: $contact_id";
    $headers = "From: $email\r\nReply-To: $email\r\n";

    $mail_sent = false;
    try {
        $mail_sent = @mail($to, $subject, $message, $headers);
    } catch (Throwable $e) {
        $mail_sent = false;
    }

    // Fallback : log si mail() ne marche pas en local
    if (!$mail_sent) {
        $logLine = date('c') . " | to=$to | subject=$subject | from=$email | id=$contact_id\n";
        file_put_contents(__DIR__ . '/../storage/mails.log', $logLine, FILE_APPEND);
    }

    json_response(201, [
        "message" => "Message contact enregistré",
        "contact_id" => $contact_id,
        "mail_sent" => $mail_sent,
        "fallback_log" => !$mail_sent ? "backend/storage/mails.log" : null
    ]);
} catch (Throwable $e) {
    json_response(500, ["error" => "Erreur serveur", "details" => $e->getMessage()]);
}
