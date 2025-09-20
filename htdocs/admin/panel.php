<?php
// /htdocs/admin/panel.php
require_once __DIR__ . '/../private/auth.php';
require_login();

$user = current_user();
$isAdmin = is_admin();
if (!$isAdmin) {
  // Veli bu sayfayı sadece "Kitap Ekle/Güncelle" için kullanıyordu;
  // yeni gereksinimde panel admin odaklı, veliyi ana sayfaya yönlendirelim.
  header('Location: /'); exit;
}

// Çocuk listesi (admin tümünü görür)
$st = $pdo->query("SELECT id, name, school_number, group_name FROM kids ORDER BY name ASC");
$kids = $st->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <title>Admin Paneli | Çocuk Kitap Takip</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body { background:#f8f9fa; }
    .tab-pane { padding-top: 1rem; }
    .form-section { max-width: 720px; }
  </style>
</head>
<body>
  <nav class="navbar navbar-dark bg-primary">
    <div class="container-fluid">
      <span class="navbar-brand">Admin Paneli</span>
      <div class="d-flex align-items-center gap-2">
        <a class="btn btn-sm btn-outline-light" href="/">Ana Sayfa</a>
        <a class="btn btn-sm btn-outline-light" href="/password_change.php"><i class="bi bi-key me-1"></i>Şifre Değiştir</a>
        <a class="btn btn-sm btn-outline-light" href="/admin/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Çıkış</a>
      </div>
    </div>
  </nav>

  <div class="container my-4">
    <ul class="nav nav-tabs" id="adminTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="user-tab" data-bs-toggle="tab" data-bs-target="#user-pane" type="button" role="tab">Kullanıcı Ekle</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="book-tab" data-bs-toggle="tab" data-bs-target="#book-pane" type="button" role="tab">Kitap Ekle</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="read-tab" data-bs-toggle="tab" data-bs-target="#read-pane" type="button" role="tab">Okuma Ekle</button>
      </li>
    </ul>

    <div class="tab-content">
      <!-- Kullanıcı Ekle -->
      <div class="tab-pane fade show active" id="user-pane" role="tabpanel" aria-labelledby="user-tab">
        <div class="form-section mt-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title mb-3">Veli + Çocuk Ekle</h5>
              <form id="userKidForm">
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label">Veli Kullanıcı Adı</label>
                    <input name="parent_username" class="form-control" required />
                  </div>
                  <div class="col-12">
                    <label class="form-label">Veli Geçici Şifre</label>
                    <input name="temp_password" type="text" class="form-control" required />
                    <div class="form-text">Veli ilk girişten sonra şifresini değiştirebilir (manuel akış).</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Çocuk Adı</label>
                    <input name="kid_name" class="form-control" required />
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Okul Numarası</label>
                    <input name="school_number" class="form-control" />
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Grup</label>
                    <input name="group_name" class="form-control" />
                  </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                  <button type="submit" class="btn btn-primary">Kaydet</button>
                  <span id="userKidMsg" class="small text-muted"></span>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Kitap Ekle -->
      <div class="tab-pane fade" id="book-pane" role="tabpanel" aria-labelledby="book-tab">
        <div class="form-section mt-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title mb-3">Yeni Kitap Ekle</h5>
              <form id="bookForm">
                <div class="mb-3">
                  <label class="form-label">Başlık</label>
                  <input name="title" class="form-control" required />
                </div>
                <div class="mb-3">
                  <label class="form-label">Yazar</label>
                  <input name="author" class="form-control" />
                </div>
                <div class="mb-3">
                  <label class="form-label">Yayınevi</label>
                  <input name="publisher" class="form-control" />
                </div>
                <div class="mb-3">
                  <label class="form-label">ISBN (opsiyonel)</label>
                  <input name="isbn" class="form-control" />
                </div>
                <div class="d-flex gap-2">
                  <button class="btn btn-success">Ekle</button>
                  <span id="bookMsg" class="small text-muted"></span>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Okuma Ekle -->
      <div class="tab-pane fade" id="read-pane" role="tabpanel" aria-labelledby="read-tab">
        <div class="form-section mt-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title mb-3">Çocuğa Okunan Kitap Ekle</h5>
              <form id="readForm">
                <div class="mb-3">
                  <label class="form-label">Çocuk</label>
                  <select name="kid_id" class="form-select" required>
                    <option value="">Seçiniz...</option>
                    <?php foreach ($kids as $k): ?>
                      <option value="<?= (int)$k['id'] ?>">
                        <?= htmlspecialchars($k['name']) ?>
                        <?= $k['school_number'] ? ' · ' . htmlspecialchars($k['school_number']) : '' ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">Kitap</label>
                  <input id="bookQuery" class="form-control" placeholder="Başlık / yazar / yayınevi ile ara" autocomplete="off" />
                  <input type="hidden" name="book_id" id="bookIdHidden" />
                  <div id="bookSuggest" class="list-group mt-2"></div>
                  <div class="form-text">Listeden seçin. (Gerekirse önce “Kitap Ekle” sekmesinden kitabı ekleyin.)</div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Okuma Tarihi</label>
                  <input name="read_date" type="date" class="form-control" />
                </div>

                <div class="d-flex gap-2">
                  <button class="btn btn-primary">Kaydet</button>
                  <span id="readMsg" class="small text-muted"></span>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

    </div> <!-- tab-content -->
  </div> <!-- container -->

  <!-- Onay Modalı (Veli zaten var ise ilişkilendir) -->
  <div class="modal fade" id="confirmLinkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Mevcut Veli Kullanıcısı</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
        </div>
        <div class="modal-body">
          Bu kullanıcı adıyla bir veli zaten kayıtlı. Yeni veli yaratmadan, çocuğu bu veli ile ilişkilendirmek ister misiniz?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgeç</button>
          <button type="button" class="btn btn-primary" id="confirmLinkBtn">Evet, ilişkilendir</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
      console.log('DOM loaded, initializing forms...');
      
      const byId = (id) => document.getElementById(id);

      // ----- Kullanıcı + Çocuk Ekle -----
      const userKidForm = byId('userKidForm');
      const userKidMsg  = byId('userKidMsg');
      
      // Debug: Check if elements are found
      console.log('userKidForm found:', !!userKidForm);
      console.log('userKidMsg found:', !!userKidMsg);
      
      if (!userKidForm) {
        console.error('userKidForm not found!');
        return;
      }
      
      const confirmModalEl = byId('confirmLinkModal');
      const confirmModal = new bootstrap.Modal(confirmModalEl);
      const confirmBtn = byId('confirmLinkBtn');
      let pendingUserKidPayload = null;

      userKidForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        console.log('Form submitted!'); // Debug log
        userKidMsg.textContent = 'Kaydediliyor...';
        
        const formData = new FormData(userKidForm);
        console.log('Form data:', Object.fromEntries(formData)); // Debug log
        
        try {
          const res = await fetch('/api/admin_create_parent_and_kid.php', { method:'POST', body: formData });
          console.log('Response status:', res.status); // Debug log
          
          const data = await res.json();
          console.log('Response data:', data); // Debug log
          
          if (data.ok) {
            userKidMsg.textContent = 'Başarıyla kaydedildi.';
            userKidForm.reset();
          } else if (data.code === 'USER_EXISTS') {
            // Onay iste
            pendingUserKidPayload = formData;
            userKidMsg.textContent = 'Bu kullanıcı adı zaten mevcut.';
            confirmModal.show();
          } else {
            userKidMsg.textContent = 'Hata: ' + (data.error || 'Bilinmeyen hata');
          }
        } catch (error) {
          console.error('Fetch error:', error); // Debug log
          userKidMsg.textContent = 'Bağlantı hatası: ' + error.message;
        }
      });

      confirmBtn?.addEventListener('click', async () => {
        if (!pendingUserKidPayload) return;
        pendingUserKidPayload.set('confirm_link', '1');
        const res = await fetch('/api/admin_create_parent_and_kid.php', { method:'POST', body: pendingUserKidPayload });
        const data = await res.json();
        confirmModal.hide();
        if (data.ok) {
          userKidMsg.textContent = 'Mevcut veli ile ilişkilendirildi.';
          userKidForm.reset();
        } else {
          userKidMsg.textContent = 'Hata: ' + (data.error || 'Bilinmeyen hata');
        }
        pendingUserKidPayload = null;
      });

      // ----- Kitap Ekle -----
      const bookForm = byId('bookForm');
      const bookMsg  = byId('bookMsg');
      if (bookForm) {
        bookForm.addEventListener('submit', async (e) => {
          e.preventDefault();
          bookMsg.textContent = 'Kaydediliyor...';
          const fd = new FormData(bookForm);
          const res = await fetch('/api/book_add.php', { method:'POST', body: fd });
          const data = await res.json();
          if (data.ok) {
            bookMsg.textContent = data.exists ? 'Zaten vardı (ID: '+data.book_id+').' : 'Eklendi (ID: '+data.book_id+').';
            bookForm.reset();
          } else {
            bookMsg.textContent = 'Hata: ' + (data.error || 'Bilinmeyen hata');
          }
        });
      }

      // ----- Okuma Ekle -----
      const readForm  = byId('readForm');
      const readMsg   = byId('readMsg');
      const bookQuery = byId('bookQuery');
      const bookIdHidden = byId('bookIdHidden');
      const bookSuggest = byId('bookSuggest');

      const debounce = (fn, ms=300) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

      const searchBooks = async (q) => {
        bookSuggest.innerHTML = '<div class="list-group-item small text-muted">Aranıyor...</div>';
        const url = new URL(location.origin + '/api/books_suggest.php');
        url.searchParams.set('query', q || '');
        url.searchParams.set('limit', '10');
        const res = await fetch(url);
        const data = await res.json();
        bookSuggest.innerHTML = '';
        if (!data.ok) {
          bookSuggest.innerHTML = '<div class="list-group-item text-danger">Hata oluştu</div>';
          return;
        }
        if (!data.data.length) {
          bookSuggest.innerHTML = '<div class="list-group-item small text-muted">Sonuç yok</div>';
          return;
        }
        data.data.forEach(row => {
          const a = document.createElement('a');
          a.href = '#';
          a.className = 'list-group-item list-group-item-action';
          a.textContent = `${row.title} — ${row.author || '—'} · ${row.publisher || '—'}`;
          a.addEventListener('click', (ev) => {
            ev.preventDefault();
            bookIdHidden.value = row.id;
            bookQuery.value = row.title;
            bookSuggest.innerHTML = '';
          });
          bookSuggest.appendChild(a);
        });
      };

      const debouncedSearchBooks = debounce(()=>searchBooks(bookQuery.value.trim()), 300);
      bookQuery?.addEventListener('input', debouncedSearchBooks);

      if (readForm) {
        readForm.addEventListener('submit', async (e) => {
          e.preventDefault();
          readMsg.textContent = 'Kaydediliyor...';
          const fd = new FormData(readForm);
          // kid_books_add.php mevcut (admin serbest)
          const res = await fetch('/api/kid_books_add.php', { method:'POST', body: fd });
          const data = await res.json();
          if (data.ok) {
            readMsg.textContent = 'Okuma kaydı eklendi.';
            readForm.reset();
            bookSuggest.innerHTML = '';
          } else {
            readMsg.textContent = 'Hata: ' + (data.error || 'Bilinmeyen hata');
          }
        });
      }
    }); // End of DOMContentLoaded
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
