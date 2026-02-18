<?php
require __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

require_auth();

echo json_encode([
  "user" => $_SESSION['user']
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
