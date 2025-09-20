<?php
// /htdocs/api/kids.php
header('Content-Type: application/json; charset=utf-8');

// Now that /private is INSIDE htdocs:
$configPath = __DIR__ . '/../private/config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'config.php not found', 'path' => $configPath]);
    exit;
}
require_once $configPath;

// Read & sanitize inputs
$q      = isset($_GET['query']) ? trim((string)$_GET['query']) : '';
$limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page   = isset($_GET['page'])  ? max(1, (int)$_GET['page']) : 1;
$limit  = ($limit > 0 && $limit <= 100) ? $limit : 10; // cap limit
$offset = ($page - 1) * $limit;

try {
    if ($q === '') {
        // Return recent kids if no query (optional behavior)
        $stmt = $pdo->prepare("
            SELECT id, name, school_number, group_name
            FROM kids
            ORDER BY id DESC
            LIMIT :limit OFFSET :offset
        ");
    } else {
        // Search by name OR school_number
        // Use prefix + contains match: faster with indexes if query starts-with
        $like = '%' . $q . '%';
        $stmt = $pdo->prepare("
            SELECT id, name, school_number, group_name
            FROM kids
            WHERE name LIKE :like
               OR school_number LIKE :like
            ORDER BY name ASC, id ASC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':like', $like, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
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
