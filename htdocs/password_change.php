<?php
// /htdocs/password_change.php
require_once __DIR__ . '/private/auth.php';
require_login();

$user = current_user();
$error = null;
$success = null;

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['__action'] ?? '') === 'change_password') {
    $current_password = (string)($_POST['current_password'] ?? '');
    $new_password = (string)($_POST['new_password'] ?? '');
    $confirm_password = (string)($_POST['confirm_password'] ?? '');
    
    // Basic validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Lütfen tüm alanları doldurun.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Yeni şifreler eşleşmiyor.';
    } elseif (strlen($new_password) < 6) {
        $error = 'Yeni şifre en az 6 karakter olmalıdır.';
    } else {
        // Verify current password
        $st = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id LIMIT 1");
        $st->execute([':id' => $user['id']]);
        $user_data = $st->fetch(PDO::FETCH_ASSOC);
        
        if ($user_data && password_verify($current_password, $user_data['password_hash'])) {
            // Update password
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $st = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
            $st->execute([':hash' => $new_hash, ':id' => $user['id']]);
            
            if ($st->rowCount() > 0) {
                $success = 'Şifreniz başarıyla değiştirildi.';
            } else {
                $error = 'Şifre güncellenirken bir hata oluştu.';
            }
        } else {
            $error = 'Mevcut şifre hatalı.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Şifre Değiştir | Çocuk Kitap Takip</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .card { max-width: 500px; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">Şifre Değiştir</span>
            <div class="d-flex align-items-center gap-2">
                <a class="btn btn-sm btn-outline-light" href="/">
                    <i class="bi bi-house me-1"></i>Ana Sayfa
                </a>
                <a class="btn btn-sm btn-outline-light" href="/admin/logout.php">
                    <i class="bi bi-box-arrow-right me-1"></i>Çıkış
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-4 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow-sm mx-auto">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-3 text-center">Şifre Değiştir</h1>
                        <p class="text-muted text-center mb-4">
                            Hoş geldiniz, <strong><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></strong>
                        </p>

                        <?php if ($error): ?>
                            <div class="alert alert-danger py-2">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success py-2">
                                <i class="bi bi-check-circle me-2"></i>
                                <?= htmlspecialchars($success) ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" autocomplete="off">
                            <input type="hidden" name="__action" value="change_password">
                            
                            <div class="mb-3">
                                <label class="form-label">Mevcut Şifre</label>
                                <input name="current_password" type="password" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Yeni Şifre</label>
                                <input name="new_password" type="password" class="form-control" required minlength="6">
                                <div class="form-text">En az 6 karakter olmalıdır.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Yeni Şifre (Tekrar)</label>
                                <input name="confirm_password" type="password" class="form-control" required minlength="6">
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-key me-2"></i>Şifreyi Değiştir
                                </button>
                                <a href="/" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Geri Dön
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="mt-auto text-center py-3 bg-white border-top">
        <small class="text-muted">&copy; <?= date('Y') ?> Çocuk Kitap Takip</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
