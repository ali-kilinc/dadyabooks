<?php
// /htdocs/api/admin_create_parent_and_kid.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../private/auth.php';
require_login();

// Debug logging
error_log('admin_create_parent_and_kid.php called');
error_log('POST data: ' . print_r($_POST, true));

if (!is_admin()) {
  http_response_code(403);
  echo json_encode(['ok'=>false, 'error'=>'Yetki yok']);
  exit;
}

$parentUsername = trim($_POST['parent_username'] ?? '');
$tempPassword   = (string)($_POST['temp_password'] ?? '');
$kidName        = trim($_POST['kid_name'] ?? '');
$schoolNumber   = trim($_POST['school_number'] ?? '');
$groupName      = trim($_POST['group_name'] ?? '');
$confirmLink    = isset($_POST['confirm_link']) ? (string)$_POST['confirm_link'] : '0';

if ($parentUsername === '' || $tempPassword === '' || $kidName === '') {
  http_response_code(400);
  echo json_encode(['ok'=>false, 'error'=>'Zorunlu alanlar eksik.']);
  exit;
}

try {
  // Veli var mı?
  $st = $pdo->prepare("SELECT id FROM users WHERE username = :u AND role='veli' LIMIT 1");
  $st->execute([':u'=>$parentUsername]);
  $existingParentId = (int)($st->fetchColumn() ?: 0);

  if ($existingParentId && $confirmLink !== '1') {
    echo json_encode(['ok'=>false, 'code'=>'USER_EXISTS', 'message'=>'Veli zaten var', 'user_id'=>$existingParentId]);
    exit;
  }

  $pdo->beginTransaction();

  // Çocuğu ekle
  $insKid = $pdo->prepare("INSERT INTO kids (name, school_number, group_name) VALUES (:n, :s, :g)");
  $insKid->execute([':n'=>$kidName, ':s'=>$schoolNumber, ':g'=>$groupName]);
  $kidId = (int)$pdo->lastInsertId();

  // Veli ID
  if (!$existingParentId) {
    $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
    $insUser = $pdo->prepare("INSERT INTO users (username, password_hash, role, full_name) VALUES (:u, :p, 'veli', :f)");
    $insUser->execute([':u'=>$parentUsername, ':p'=>$hash, ':f'=>$parentUsername]);
    $parentId = (int)$pdo->lastInsertId();
  } else {
    $parentId = $existingParentId;
  }

  // İlişki ekle (varsa atla)
  $link = $pdo->prepare("INSERT IGNORE INTO parent_kids (user_id, kid_id) VALUES (:u, :k)");
  $link->execute([':u'=>$parentId, ':k'=>$kidId]);

  $pdo->commit();
  echo json_encode(['ok'=>true, 'kid_id'=>$kidId, 'parent_id'=>$parentId, 'linked_existing_user'=>$existingParentId?true:false]);
} catch (Throwable $e) {
  $pdo->rollBack();
  error_log('Database error in admin_create_parent_and_kid.php: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['ok'=>false, 'error'=>'Kayıt başarısız', 'details'=>$e->getMessage()]);
}
