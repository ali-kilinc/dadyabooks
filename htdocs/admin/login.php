<?php
// /htdocs/admin/login.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../private/config.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($username !== '' && $password !== '') {
        $st = $pdo->prepare("SELECT id, username, password_hash, role, full_name FROM users WHERE username = :u LIMIT 1");
        $st->execute([':u'=>$username]);
        $user = $st->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'full_name' => $user['full_name'] ?? null
            ];
            header('Location: /admin/panel.php');
            exit;
        } else {
            $error = 'Kullanıcı adı veya şifre hatalı.';
        }
    } else {
        $error = 'Lütfen kullanıcı adı ve şifre girin.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Giriş | Çocuk Kitap Takip</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-10 col-md-6 col-lg-4">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h1 class="h4 mb-3 text-center">Çocuk Kitap Takip</h1>
            <?php if ($error): ?>
              <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" autocomplete="off">
              <div class="mb-3">
                <label class="form-label">Kullanıcı Adı</label>
                <input name="username" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Şifre</label>
                <input name="password" type="password" class="form-control" required>
              </div>
              <button class="btn btn-primary w-100">Giriş Yap</button>
            </form>
          </div>
        </div>
        <p class="text-center text-muted small mt-3">&copy; <?= date('Y') ?></p>
      </div>
    </div>
  </div>
</body>
</html>
