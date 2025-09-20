<?php
// /htdocs/api/my_kids.php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../private/config.php';

$user = $_SESSION['user'] ?? null;
if (!$user) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Giriş gerekli']); exit; }
if ($user['role'] !== 'veli') { echo json_encode(['ok'=>true,'data'=>[]]); exit; }

try {
  $st = $pdo->prepare("
    SELECT k.id, k.name, k.school_number, k.group_name
    FROM parent_kids pk
    JOIN kids k ON k.id = pk.kid_id
    WHERE pk.user_id = :u
    ORDER BY k.name ASC
  ");
  $st->execute([':u'=>$user['id']]);
  echo json_encode(['ok'=>true, 'data'=>$st->fetchAll()]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Sorgu hatası','details'=>$e->getMessage()]);
}
