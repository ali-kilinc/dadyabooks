<?php
// /htdocs/api/books_suggest.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../private/auth.php';
require_login();

$q = trim($_GET['query'] ?? '');
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$limit = ($limit>0 && $limit<=50) ? $limit : 10;

try {
  if ($q === '') {
    $st = $pdo->prepare("SELECT id, title, author, publisher FROM books ORDER BY id DESC LIMIT :lim");
  } else {
    $like = '%'.$q.'%';
    $st = $pdo->prepare("
      SELECT id, title, author, publisher
      FROM books
      WHERE title LIKE :like OR author LIKE :like OR publisher LIKE :like
      ORDER BY title ASC
      LIMIT :lim
    ");
    $st->bindValue(':like', $like, PDO::PARAM_STR);
  }
  $st->bindValue(':lim', $limit, PDO::PARAM_INT);
  $st->execute();
  echo json_encode(['ok'=>true,'data'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Sorgu hatası','details'=>$e->getMessage()]);
}
