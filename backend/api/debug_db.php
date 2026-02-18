<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

try {
  $pdo = db();
  $db = $pdo->query("SELECT DATABASE()")->fetchColumn();
  $count = $pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();

  echo json_encode([
    "DB_HOST" => getenv("DB_HOST"),
    "DB_NAME_env" => getenv("DB_NAME"),
    "database_used" => $db,
    "contacts_count" => (int)$count
  ], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    "error"=>"Erreur serveur",
    "details"=>$e->getMessage()
  ], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
}
