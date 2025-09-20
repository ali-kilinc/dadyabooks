<?php
// /htdocs/api/books.php
header('Content-Type: application/json; charset=utf-8');

// Now that /private is INSIDE htdocs:
$configPath = __DIR__ . '/../private/config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'config.php not found', 'path' => $configPath]);
    exit;
}
require_once $configPath;

// Read & validate inputs
$kidId  = isset($_GET['kid_id']) ? (int)$_GET['kid_id'] : 0;
$q      = isset($_GET['query'])  ? trim((string)$_GET['query']) : '';
$limit  = isset($_GET['limit'])  ? (int)$_GET['limit'] : 20;
$page   = isset($_GET['page'])   ? max(1, (int)$_GET['page']) : 1;
$limit  = ($limit > 0 && $limit <= 100) ? $limit : 20; // cap limit
$offset = ($page - 1) * $limit;

if ($kidId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'kid_id is required']);
    exit;
}

try {
    if ($q === '') {
        $sql = "
            SELECT b.id, b.title, b.isbn, b.author, b.publisher, kb.read_date
            FROM kid_books kb
            JOIN books b ON b.id = kb.book_id
            WHERE kb.kid_id = :kid_id
            ORDER BY kb.read_date DESC, b.title ASC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $pdo->prepare($sql);
    } else {
        $like = '%' . $q . '%';
        $sql = "
            SELECT b.id, b.title, b.isbn, b.author, b.publisher, kb.read_date
            FROM kid_books kb
            JOIN books b ON b.id = kb.book_id
            WHERE kb.kid_id = :kid_id
              AND (
                    b.title     LIKE :like
                 OR b.author    LIKE :like
                 OR b.publisher LIKE :like
              )
            ORDER BY kb.read_date DESC, b.title ASC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':like', $like, PDO::PARAM_STR);
    }

    $stmt->bindValue(':kid_id', $kidId, PDO::PARAM_INT);
    $stmt->bindValue(':limit',  $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll();

    echo json_encode([
        'ok' => true,
        'data' => $rows,
        'pagination' => [
            'page'  => $page,
            'limit' => $limit
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Query failed', 'details' => $e->getMessage()]);
}
