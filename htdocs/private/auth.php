<?php
// /htdocs/private/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// $pdo'yu config'ten al
require_once __DIR__ . '/config.php';

function current_user() {
    return $_SESSION['user'] ?? null; // ['id'=>, 'username'=>, 'role'=>, 'full_name'=>]
}

function require_login() {
    if (!current_user()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function is_admin() {
    $u = current_user();
    return $u && $u['role'] === 'admin';
}

function is_parent() {
    $u = current_user();
    return $u && $u['role'] === 'veli';
}

// Veli bu kid'e bağlı mı?
function parent_owns_kid($userId, $kidId, PDO $pdo) {
    $st = $pdo->prepare("SELECT 1 FROM parent_kids WHERE user_id = :u AND kid_id = :k LIMIT 1");
    $st->execute([':u'=>$userId, ':k'=>$kidId]);
    return (bool)$st->fetchColumn();
}
