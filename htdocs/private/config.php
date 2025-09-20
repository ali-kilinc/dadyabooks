<?php
// /private/config.php

// --- Database credentials (InfinityFree cPanel -> MySQL Databases) ---
$dbHost = 'sql312.infinityfree.com';     // e.g. sqlXXX.epizy.com
$dbName = 'if0_39985637_dadyabook';     // e.g. epiz_12345678_db
$dbUser = 'if0_39985637';     // e.g. epiz_12345678
$dbPass = 'yguANkU7WH85q8'; // your password

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_turkish_ci"
];

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        $options
    );
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => false,
        'error' => 'Database connection failed',
        'details' => $e->getMessage()
    ]);
    exit;
}
