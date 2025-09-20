<?php
// /htdocs/api/kid_books_delete.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../private/auth.php';
require_login();

if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['ok'=>false, 'error'=>'Silme yetkiniz yok.']);
    exit;
}

$kid_id  = (int)($_POST['kid_id'] ?? 0);
$book_id = (int)($_POST['book_id'] ?? 0);

if ($kid_id<=0 || $book_id<=0) {
    http_response_code(400);
    echo json_encode(['ok'=>false, 'error'=>'kid_id ve book_id zorunludur.']);
    exit;
}

try {
    $del = $pdo->prepare("DELETE FROM kid_books WHERE kid_id=:k AND book_id=:b");
    $del->execute([':k'=>$kid_id, ':b'=>$book_id]);
    echo json_encode(['ok'=>true, 'deleted'=>$del->rowCount()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false, 'error'=>'Silme başarısız', 'details'=>$e->getMessage()]);
}
