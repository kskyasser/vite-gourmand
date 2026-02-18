<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
  $pdo = db();

  $stmt = $pdo->query("
    SELECT id, email, titre, description, created_at
    FROM contacts
    ORDER BY created_at DESC
  ");

  $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($contacts, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    "error" => "Erreur serveur",
    "details" => $e->getMessage()
  ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
