<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_auth(): void {
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(["error" => "Non authentifié"]);
        exit;
    }
}

function require_role(array $roles): void {
    require_auth();
    if (!in_array($_SESSION['user']['role'], $roles, true)) {
        http_response_code(403);
        echo json_encode(["error" => "Accès interdit"]);
        exit;
    }
}

function current_user(): ?array {
  return $_SESSION['user'] ?? null;
}
