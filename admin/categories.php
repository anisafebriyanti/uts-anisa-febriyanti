<?php
/**
 * FoodFinder Admin - Manajemen Kategori Makanan
 */

require_once __DIR__ . '/auth_check.php';

$error = '';

// Handle Tambah / Edit Kategori (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fa-utensils');
        $desc = trim($_POST['description'] ?? '');

        if (empty($name)) {
            $error = 'Nama kategori wajib diisi.';
        } else {
            $slug = slugify($name);
            $check = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
            $check->execute([$slug]);
            if ($check->fetch()) {
                $slug .= '-' . rand(10, 99);
            }

            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, icon, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $icon, $desc]);
            set_flash('success', 'Kategori baru "' . e($name) . '" berhasil ditambahkan.');
            header('Location: ' . BASE_URL . 'admin/categories.php');
            exit;
        }
    } elseif ($action === 'update') {
        $catId = (int) ($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fa-utensils');
        $desc = trim($_POST['description'] ?? '');

        if ($catId > 0 && !empty($name)) {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, icon = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $icon, $desc, $catId]);
            set_flash('success', 'Kategori "' . e($name) . '" berhasil diperbarui.');
        } else {
            set_flash('danger', 'Nama kategori tidak boleh kosong.');
        }
        header('Location: ' . BASE_URL . 'admin/categories.php');
        exit;
    } elseif ($action === 'delete') {
        $catId = (int) ($_POST['category_id'] ?? 0);
        if ($catId > 0) {
            // Cek apakah masih ada tempat makan yang menggunakan kategori ini
            $checkResto = $pdo->prepare("SELECT COUNT(*) FROM restaurants WHERE category_id = ?");
            $checkResto->execute([$catId]);
            $count = (int) $checkResto->fetchColumn();

            if ($count > 0) {
                set_flash('danger', 'Tidak dapat menghapus kategori ini karena masih digunakan oleh ' . $count . ' tempat makan. Silakan pindahkan atau hapus tempat makan tersebut terlebih dahulu.');
            } else {
                $del = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                $del->execute([$catId]);
                set_flash('success', 'Kategori berhasil dihapus.');
            }
        }
        header('Location: ' . BASE_URL . 'admin/categories.php');
        exit;
    }
}

// Ambil semua kategori beserta jumlah tempat makannya
$categories = $pdo->query("
    SELECT c.*, COUNT(r.id) as total_restaurants 
    FROM categories c 
    LEFT JOIN restaurants r ON c.id = r.category_id 
    GROUP BY c.id 
    ORDER BY c.id ASC
")->fetchAll();

$pageTitle = 'Manajemen Kategori Makanan';
$pageDesc = 'Kelola kategori kuliner untuk memudahkan klasifikasi pencarian';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> - Admin FoodFinder</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
</head>
<body class="admin-body">

  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="admin-main">
    <?php include __DIR__ . '/topbar.php'; ?>

    <main class="admin-content">
      
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
          <i class="fas fa-circle-exclamation"></i> <?= e($error) ?>
        </div>
      <?php endif; ?>

      <div style="display: grid; grid-template-columns: 360px 1fr; gap: 30px; align-items: flex-start;">
        
        <!-- Formulir Tambah Kategori -->
        <div class="admin-panel">
          <div class="admin-panel-header">
            <h3><i class="fas fa-plus-circle text-pink"></i> Tambah Kategori</h3>
          </div>
          <div style="padding: 24px;">
            <form action="<?= BASE_URL ?>admin/categories.php" method="POST">
              <input type="hidden" name="action" value="create">

              <div class="form-group">
                <label for="catName">Nama Kategori *</label>
                <input type="text" name="name" id="catName" class="form-control" placeholder="Contoh: Street Food & Angkringan" required>
              </div>

              <div class="form-group">
                <label for="catIcon">Ikon Font Awesome *</label>
                <input type="text" name="icon" id="catIcon" class="form-control" placeholder="fa-bowl-rice" value="fa-utensils" required>
                <small style="color: var(--admin-text-muted);">Contoh: <code>fa-utensils</code>, <code>fa-mug-hot</code>, <code>fa-burger</code>, <code>fa-ice-cream</code></small>
              </div>

              <div class="form-group">
                <label for="catDesc">Deskripsi Singkat</label>
                <textarea name="description" id="catDesc" rows="3" class="form-control" placeholder="Keterangan singkat tentang kategori ini..."></textarea>
              </div>

              <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-save"></i> Simpan Kategori
              </button>
            </form>
          </div>
        </div>

        <!-- Tabel Daftar Kategori -->
        <div class="admin-panel">
          <div class="admin-panel-header">
            <h3><i class="fas fa-layer-group text-pink"></i> Daftar Kategori (<?= count($categories) ?>)</h3>
          </div>

          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Ikon</th>
                  <th>Nama Kategori</th>
                  <th>Slug URL</th>
                  <th>Jumlah Tempat</th>
                  <th style="text-align: right;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($categories as $cat): ?>
                  <tr>
                    <td>
                      <div class="category-icon-box" style="width: 40px; height: 40px; font-size: 1.1rem; border-radius: 10px;">
                        <i class="fas <?= e($cat['icon']) ?>"></i>
                      </div>
                    </td>
                    <td>
                      <strong><?= e($cat['name']) ?></strong>
                      <?php if (!empty($cat['description'])): ?>
                        <div style="font-size: 0.78rem; color: var(--admin-text-muted); max-width: 280px;">
                          <?= e($cat['description']) ?>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <code><?= e($cat['slug']) ?></code>
                    </td>
                    <td>
                      <span class="badge badge-pink">
                        <?= (int)$cat['total_restaurants'] ?> Resto
                      </span>
                    </td>
                    <td style="text-align: right;">
                      <div class="action-btns" style="justify-content: flex-end;">
                        <!-- Tombol Hapus -->
                        <form action="<?= BASE_URL ?>admin/categories.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori <?= e(addslashes($cat['name'])) ?>?')">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="category_id" value="<?= (int)$cat['id'] ?>">
                          <button type="submit" class="btn-table-action btn-table-delete" title="Hapus Kategori">
                            <i class="fas fa-trash-can"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </main>
  </div>

  <script src="<?= BASE_URL ?>assets/js/script.js"></script>
</body>
</html>
