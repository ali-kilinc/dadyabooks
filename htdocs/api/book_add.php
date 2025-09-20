<?php
// /htdocs/api/book_add.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../private/auth.php';
require_login();

if (!is_admin()) {
  http_response_code(403);
  echo json_encode(['ok'=>false,'error'=>'Yetki yok']);
  exit;
}

$title = trim($_POST['title'] ?? '');
$author = trim($_POST['author'] ?? '');
$publisher = trim($_POST['publisher'] ?? '');
$isbn = trim($_POST['isbn'] ?? '');

if ($title === '') {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Başlık zorunlu.']);
  exit;
}

try {
  $st = $pdo->prepare("SELECT id FROM books WHERE title=:t AND IFNULL(author,'')=:a AND IFNULL(publisher,'')=:p AND IFNULL(isbn,'')=:i LIMIT 1");
  $st->execute([':t'=>$title, ':a'=>$author, ':p'=>$publisher, ':i'=>$isbn]);
  $bookId = (int)($st->fetchColumn() ?: 0);

  if ($bookId) {
    echo json_encode(['ok'=>true,'exists'=>true,'book_id'=>$bookId]);
    return;
  }

  $ins = $pdo->prepare("INSERT INTO books (title, isbn, author, publisher) VALUES (:t,:i,:a,:p)");
  $ins->execute([':t'=>$title, ':i'=>$isbn, ':a'=>$author, ':p'=>$publisher]);
  $bookId = (int)$pdo->lastInsertId();

  echo json_encode(['ok'=>true,'exists'=>false,'book_id'=>$bookId]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Kayıt başarısız','details'=>$e->getMessage()]);
}
