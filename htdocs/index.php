<?php
// /htdocs/index.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/private/config.php';

$error = null;

// Basit auth helperları (login.php yerine burada)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['__action'] ?? '') === 'login') {
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
      // POST-redirect-GET
      header('Location: /');
      exit;
    } else {
      $error = 'Kullanıcı adı veya şifre hatalı.';
    }
  } else {
    $error = 'Lütfen kullanıcı adı ve şifre girin.';
  }
}

$user = $_SESSION['user'] ?? null;
$isAdmin = $user && $user['role'] === 'admin';
$isParent = $user && $user['role'] === 'veli';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Çocuk Kitap Takip</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background:#f8f9fa; }
    .search-box input { border-radius: 20px; padding-left: 1rem; }
    .panel { padding: 1rem; }
    .list-group-item-action { cursor: pointer; }
    .sticky-header { position: sticky; top: 0; z-index: 1020; }
    @media (min-width: 768px){
      .vh-md-100 { min-height: calc(100vh - 56px - 56px); }
      .panel { padding: 1.25rem; }
    }
    .icon-link { text-decoration:none; }
  </style>
</head>
<body class="d-flex flex-column min-vh-100">

  <nav class="navbar navbar-dark bg-primary sticky-header">
    <div class="container-fluid">
      <span class="navbar-brand mb-0 h1">Çocuk Kitap Takip</span>

      <?php if ($user): ?>
        <div class="d-flex align-items-center gap-3">
          <!-- Rol bazlı kısayollar -->
          <?php if ($isAdmin): ?>
            <a class="icon-link text-white" href="/admin/panel.php" title="Admin Paneli">
              <i class="bi bi-speedometer2 fs-5"></i>
            </a>
          <?php elseif ($isParent): ?>
            <a class="icon-link text-white" href="/admin/panel.php" title="Kitap Ekle/Güncelle">
              <i class="bi bi-journal-plus fs-5"></i>
            </a>
          <?php endif; ?>
          <a class="icon-link text-white" href="/password_change.php" title="Şifre Değiştir">
            <i class="bi bi-key fs-5"></i>
          </a>
          <a class="icon-link text-white" href="/admin/logout.php" title="Çıkış">
            <i class="bi bi-box-arrow-right fs-5"></i>
          </a>
        </div>
      <?php endif; ?>
    </div>
  </nav>

  <div class="container my-4 flex-grow-1">

    <?php if (!$user): ?>
      <!-- GİRİŞ EKRANI -->
      <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-6 col-lg-4">
          <div class="card shadow-sm">
            <div class="card-body p-4">
              <h1 class="h4 mb-3 text-center">Giriş Yap</h1>
              <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
              <?php endif; ?>
              <form method="post" autocomplete="off">
                <input type="hidden" name="__action" value="login">
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
              <p class="small text-muted mt-3 text-center">
                Admin tüm çocukları görür. Veli yalnızca kendi çocuğunu görür.
              </p>
            </div>
          </div>
        </div>
      </div>

    <?php else: ?>
      <!-- ROL-BAZLI ANA EKRAN -->
      <div class="row g-3">
        <!-- Sol panel: Çocuk seçimi -->
        <div class="col-12 col-md-4">
          <div class="card shadow-sm vh-md-100">
            <div class="card-body panel">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="card-title mb-0">Çocuk Seçimi</h5>
                <span class="badge text-bg-secondary">
                  <?= $isAdmin ? 'Admin' : 'Veli' ?>
                </span>
              </div>

              <div class="search-box mb-3">
                <input id="kidSearch" type="text" class="form-control"
                       placeholder="<?= $isAdmin ? 'Ad veya okul no ile ara' : 'Çocuğunuz listelenir' ?>"
                       autocomplete="off" <?= $isParent ? 'disabled' : '' ?>>
              </div>

              <div id="kidState" class="mb-2 small text-muted"></div>
              <ul id="kidList" class="list-group"></ul>
            </div>
          </div>
        </div>

        <!-- Sağ panel: Kitap listesi -->
        <div class="col-12 col-md-8">
          <div class="card shadow-sm vh-md-100">
            <div class="card-body panel">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="card-title mb-0">Okuduğu Kitaplar</h5>
                <span id="currentKidBadge" class="badge text-bg-secondary d-none"></span>
              </div>

              <div class="search-box mb-3">
                <input id="bookSearch" type="text" class="form-control"
                       placeholder="Başlık, yazar veya yayınevine göre ara"
                       autocomplete="off" disabled>
              </div>

              <div id="bookState" class="mb-2 small text-muted"></div>
              <ul id="bookList" class="list-group"></ul>

              <div class="mt-3 d-flex gap-2">
                <?php if ($isAdmin): ?>
                  <a class="btn btn-outline-primary btn-sm" href="/admin/panel.php">
                    <i class="bi bi-speedometer2 me-1"></i> Admin Paneli
                  </a>
                <?php else: ?>
                  <a class="btn btn-outline-success btn-sm" href="/admin/panel.php">
                    <i class="bi bi-journal-plus me-1"></i> Kitap Ekle/Güncelle
                  </a>
                <?php endif; ?>
                <a class="btn btn-outline-secondary btn-sm" href="/admin/logout.php">
                  <i class="bi bi-box-arrow-right me-1"></i> Çıkış
                </a>
              </div>

            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

  </div>

  <footer class="mt-auto text-center py-3 bg-white border-top">
    <small class="text-muted">&copy; <?= date('Y') ?> Çocuk Kitap Takip</small>
  </footer>

<?php if ($user): ?>
  <script>
    // ----- Helpers -----
    const qs = (s, el=document) => el.querySelector(s);
    const debounce = (fn, wait=300) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), wait); }; };
    const fmtDate = (v) => {
      if (!v) return '';
      try { const d = new Date(v); if(!isNaN(d)) return d.toLocaleDateString('tr-TR'); } catch(_){}
      return v;
    };

    // DOM
    const kidSearch = qs('#kidSearch');
    const kidList   = qs('#kidList');
    const kidState  = qs('#kidState');
    const bookSearch= qs('#bookSearch');
    const bookList  = qs('#bookList');
    const bookState = qs('#bookState');
    const currentKidBadge = qs('#currentKidBadge');

    let selectedKid = null;
    const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;

    // ----- Kids fetch -----
    const fetchKidsAdmin = async (query='') => {
      kidState.textContent = 'Yükleniyor...';
      kidList.innerHTML = '';
      try {
        const url = new URL(location.origin + '/api/kids.php');
        if (query) url.searchParams.set('query', query);
        url.searchParams.set('limit','20');
        const res = await fetch(url);
        const data = await res.json();
        if (!data.ok) throw new Error(data.error||'Hata');
        renderKidResults(data.data);
        kidState.textContent = data.data.length ? `${data.data.length} sonuç` : (query ? 'Sonuç bulunamadı.' : 'Henüz kayıt yok.');
      } catch(e){ console.error(e); kidState.textContent='Hata oluştu.'; }
    };

    const fetchKidsParent = async () => {
      kidState.textContent = 'Yükleniyor...';
      kidList.innerHTML = '';
      try {
        const res = await fetch('/api/my_kids.php');
        const data = await res.json();
        if (!data.ok) throw new Error(data.error||'Hata');
        renderKidResults(data.data);
        kidState.textContent = data.data.length ? `${data.data.length} çocuk` : 'Tanımlı çocuk yok.';
        // Veli ise ilk çocuğu otomatik seç
        if (data.data.length) selectKid(data.data[0]);
      } catch(e){ console.error(e); kidState.textContent='Hata oluştu.'; }
    };

    const renderKidResults = (rows=[]) => {
      kidList.innerHTML = '';
      rows.forEach(row => {
        const li = document.createElement('li');
        li.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
        li.innerHTML = `
          <div>
            <div class="fw-semibold">${row.name || '—'}</div>
            <div class="small text-muted">Okul No: ${row.school_number || '—'}${row.group_name ? ' · Grup: ' + row.group_name : ''}</div>
          </div>
          <span class="badge text-bg-primary">Seç</span>
        `;
        li.addEventListener('click', ()=>selectKid(row));
        kidList.appendChild(li);
      });
    };

    const selectKid = (kid) => {
      selectedKid = kid;
      currentKidBadge.textContent = `${kid.name}${kid.school_number ? ' · ' + kid.school_number : ''}`;
      currentKidBadge.classList.remove('d-none');
      bookSearch.disabled = false;
      bookSearch.value = '';
      bookSearch.focus();
      fetchBooksForKid(kid.id, '');
    };

    const fetchBooksForKid = async (kidId, query='') => {
      bookState.textContent = 'Yükleniyor...';
      bookList.innerHTML = '';
      try {
        const url = new URL(location.origin + '/api/books.php');
        url.searchParams.set('kid_id', kidId);
        if (query) url.searchParams.set('query', query);
        url.searchParams.set('limit','50');
        const res = await fetch(url);
        const data = await res.json();
        if (!data.ok) throw new Error(data.error||'Hata');
        renderBookResults(data.data);
        bookState.textContent = data.data.length ? `${data.data.length} kitap` : (query ? 'Eşleşme yok.' : 'Kayıtlı kitap yok.');
      } catch(e){ console.error(e); bookState.textContent='Hata oluştu.'; }
    };

    const renderBookResults = (rows=[]) => {
      bookList.innerHTML = '';
      rows.forEach(row => {
        const li = document.createElement('li');
        li.className = 'list-group-item';
        const dateBadge = row.read_date ? `<span class="badge rounded-pill text-bg-light border ms-2">${fmtDate(row.read_date)}</span>` : '';
        li.innerHTML = `
          <div class="d-flex flex-column">
            <div class="fw-semibold">${row.title || '—'} ${dateBadge}</div>
            <div class="small text-muted">${row.author || '—'} · ${row.publisher || '—'}</div>
          </div>
        `;
        bookList.appendChild(li);
      });
    };

    // Events
    if (isAdmin) {
      const debouncedKid = debounce(()=>fetchKidsAdmin(kidSearch.value.trim()), 300);
      kidSearch?.addEventListener('input', debouncedKid);
      // İlk yükleme (admin için boş sorgu → son eklenenleri getirir)
      fetchKidsAdmin('');
    } else {
      // Veli: kendi çocukları
      fetchKidsParent();
    }

    const debouncedBook = debounce(()=>{
      if (!selectedKid) return;
      fetchBooksForKid(selectedKid.id, (qs('#bookSearch').value||'').trim());
    }, 300);
    qs('#bookSearch').addEventListener('input', debouncedBook);
  </script>
<?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
