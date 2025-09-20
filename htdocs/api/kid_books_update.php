<?php
// /htdocs/api/kid_books_update.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../private/auth.php';
require_login();

$user = current_user();

$kid_id  = (int)($_POST['kid_id'] ?? 0);
$book_id = (int)($_POST['book_id'] ?? 0);
$readDate= trim($_POST['read_date'] ?? '');

if ($kid_id<=0 || $book_id<=0) {
    http_response_code(400);
    echo json_encode(['ok'=>false, 'error'=>'kid_id ve book_id zorunludur.']);
    exit;
}

try {
    // Kayıt var mı ve kim ekledi?
    $st = $pdo->prepare("SELECT created_by_user_id FROM kid_books WHERE kid_id=:k AND book_id=:b");
    $st->execute([':k'=>$kid_id, ':b'=>$book_id]);
    $owner = $st->fetchColumn();

    if (!$owner) {
        http_response_code(404);
        echo json_encode(['ok'=>false, 'error'=>'Kayıt bulunamadı.']);
        exit;
    }

    // Yetki: admin serbest; veli ise sadece kendi eklediğini güncelleyebilir
    if (!is_admin() && (int)$owner !== (int)$user['id']) {
        http_response_code(403);
        echo json_encode(['ok'=>false, 'error'=>'Bu kaydı güncelleme yetkiniz yok.']);
        exit;
    }

    $up = $pdo->prepare("UPDATE kid_books SET read_date = :d, updated_at = CURRENT_TIMESTAMP WHERE kid_id=:k AND book_id=:b");
    $up->execute([
        ':d' => $readDate !== '' ? $readDate : null,
        ':k' => $kid_id,
        ':b' => $book_id
    ]);

    echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false, 'error'=>'Güncelleme başarısız', 'details'=>$e->getMessage()]);
}
