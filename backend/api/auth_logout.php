<?php
require __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

session_destroy();
echo json_encode(["message" => "Déconnexion réussie"]);
