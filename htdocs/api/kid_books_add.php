<?php
// /htdocs/api/kid_books_add.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../private/auth.php';
require_login();

$user = current_user();

// Inputs
$kid_id   = (int)($_POST['kid_id'] ?? 0);
$book_id  = (int)($_POST['book_id'] ?? 0);
$title    = trim($_POST['title'] ?? '');
$author   = trim($_POST['author'] ?? '');
$publisher= trim($_POST['publisher'] ?? '');
$isbn     = trim($_POST['isbn'] ?? '');
$readDate = trim($_POST['read_date'] ?? ''); // 'YYYY-MM-DD' veya boş

// Check if we have book_id (preferred) or book details
if ($kid_id <= 0) {
    http_response_code(400);
    echo json_encode(['ok'=>false, 'error'=>'kid_id zorunludur.']);
    exit;
}

if ($book_id <= 0 && $title === '') {
    http_response_code(400);
    echo json_encode(['ok'=>false, 'error'=>'book_id veya başlık zorunludur.']);
    exit;
}

// Yetki: veli ise bu kid'e bağlı olmalı
if (is_parent() && !parent_owns_kid($user['id'], $kid_id, $pdo)) {
    http_response_code(403);
    echo json_encode(['ok'=>false, 'error'=>'Bu çocuk için ekleme yetkiniz yok.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $bookId = 0;

    if ($book_id > 0) {
        // Use provided book_id (preferred method)
        $bookId = $book_id;
        
        // Verify the book exists
        $st = $pdo->prepare("SELECT id FROM books WHERE id = :id LIMIT 1");
        $st->execute([':id'=>$bookId]);
        if (!$st->fetchColumn()) {
            http_response_code(400);
            echo json_encode(['ok'=>false, 'error'=>'Geçersiz kitap ID.']);
            exit;
        }
    } else {
        // Create book from details (fallback method)
        $st = $pdo->prepare("
            SELECT id FROM books
            WHERE title = :t AND IFNULL(author,'') = :a AND IFNULL(publisher,'') = :p AND IFNULL(isbn,'') = :i
            LIMIT 1
        ");
        $st->execute([
            ':t'=>$title,
            ':a'=>$author,
            ':p'=>$publisher,
            ':i'=>$isbn
        ]);
        $bookId = (int)($st->fetchColumn() ?: 0);

        if ($bookId === 0) {
            $ins = $pdo->prepare("INSERT INTO books (title, isbn, author, publisher) VALUES (:t, :i, :a, :p)");
            $ins->execute([':t'=>$title, ':i'=>$isbn, ':a'=>$author, ':p'=>$publisher]);
            $bookId = (int)$pdo->lastInsertId();
        }
    }

    // kid_books ekle (uniq(kid_id,book_id) varsa, hata verir; isterseniz IGNORE kullanabilirsiniz)
    $kb = $pdo->prepare("
      INSERT INTO kid_books (kid_id, book_id, read_date, created_by_user_id)
      VALUES (:k, :b, :d, :u)
      ON DUPLICATE KEY UPDATE read_date = VALUES(read_date), updated_at = CURRENT_TIMESTAMP
    ");
    $kb->execute([
        ':k'=>$kid_id,
        ':b'=>$bookId,
        ':d'=>$readDate !== '' ? $readDate : null,
        ':u'=>$user['id'],
    ]);

    $pdo->commit();
    echo json_encode(['ok'=>true, 'book_id'=>$bookId]);
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['ok'=>false, 'error'=>'Kayıt başarısız', 'details'=>$e->getMessage()]);
}
