<?php

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;

    // Variables d'environnement (prioritaires)
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_NAME') ?: 'vite_gourmand';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: 'root';

    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    // SSL optionnel (Aiven/Render)
    $caPath = getenv('DB_SSL_CA_PATH');
    if ($caPath && is_file($caPath)) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
    }

    $pdo = new PDO($dsn, $user, $pass, $options);
    return $pdo;
}
