<?php
/**
 * FoodFinder Admin - Manajemen Tempat Makan
 */

require_once __DIR__ . '/auth_check.php';

// Handle Hapus Tempat Makan (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $deleteId = (int) ($_POST['restaurant_id'] ?? 0);
    if ($deleteId > 0) {
        // Ambil info nama & file gambar untuk dihapus
        $find = $pdo->prepare("SELECT name, image FROM restaurants WHERE id = ?");
        $find->execute([$deleteId]);
        $target = $find->fetch();

        if ($target) {
            // Hapus record dari database (menus, reviews, favorites terhapus via CASCADE FK)
            $delStmt = $pdo->prepare("DELETE FROM restaurants WHERE id = ?");
            $delStmt->execute([$deleteId]);

            // Hapus file fisik gambar jika bukan foto default
            if (!empty($target['image']) && file_exists(__DIR__ . '/../uploads/restaurants/' . $target['image'])) {
                @unlink(__DIR__ . '/../uploads/restaurants/' . $target['image']);
            }

            set_flash('success', 'Tempat makan "' . e($target['name']) . '" berhasil dihapus dari sistem.');
        } else {
            set_flash('danger', 'Data tempat makan tidak ditemukan.');
        }
    }
    header('Location: ' . BASE_URL . 'admin/restaurants.php');
    exit;
}

// Filter & Pencarian
$search = trim($_GET['q'] ?? '');
$filterCategory = isset($_GET['category']) ? (int) $_GET['category'] : 0;

$sql = "
    SELECT r.*, c.name as category_name 
    FROM restaurants r 
    JOIN categories c ON r.category_id = c.id 
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (r.name LIKE ? OR r.food_type LIKE ? OR r.city LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($filterCategory > 0) {
    $sql .= " AND r.category_id = ?";
    $params[] = $filterCategory;
}

$sql .= " ORDER BY r.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$restaurants = $stmt->fetchAll();

// Ambil daftar kategori untuk dropdown filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$pageTitle = 'Manajemen Tempat Makan';
$pageDesc = 'Kelola informasi, kategori, menu, dan status publikasi tempat makan';
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

  <!-- Sidebar -->
  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="admin-main">
    <?php include __DIR__ . '/topbar.php'; ?>

    <main class="admin-content">
      
      <div class="admin-panel">
        <div class="admin-panel-header">
          <form action="<?= BASE_URL ?>admin/restaurants.php" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input 
              type="text" 
              name="q" 
              value="<?= e($search) ?>" 
              placeholder="Cari tempat makan..." 
              class="form-control" 
              style="width: 240px; padding: 8px 14px; font-size: 0.9rem;"
            >
            <select name="category" class="custom-select" style="font-size: 0.9rem;" onchange="this.form.submit()">
              <option value="0">Semua Kategori</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>" <?= ($filterCategory === (int)$cat['id']) ? 'selected' : '' ?>>
                  <?= e($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-outline btn-sm">Filter</button>
            <?php if (!empty($search) || $filterCategory > 0): ?>
              <a href="<?= BASE_URL ?>admin/restaurants.php" class="btn btn-sm" style="color: var(--danger);">Reset</a>
            <?php endif; ?>
          </form>

          <a href="<?= BASE_URL ?>admin/restaurant-add.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Tempat Makan
          </a>
        </div>

        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Foto & Nama Tempat</th>
                <th>Kategori</th>
                <th>Kisaran Harga</th>
                <th>Rating</th>
                <th>Status</th>
                <th style="text-align: right;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($restaurants) > 0): ?>
                <?php foreach ($restaurants as $r): ?>
                  <tr>
                    <td>
                      <div style="display: flex; align-items: center; gap: 14px;">
                        <img src="<?= get_restaurant_image($r['image']) ?>" alt="" class="table-img-thumb">
                        <div>
                          <strong>
                            <a href="<?= BASE_URL ?>restaurant-detail.php?id=<?= (int)$r['id'] ?>" target="_blank" style="color: var(--admin-text-main);">
                              <?= e($r['name']) ?>
                            </a>
                          </strong>
                          <div style="font-size: 0.8rem; color: var(--admin-text-muted);">
                            <i class="fas fa-map-marker-alt"></i> <?= e($r['city']) ?> &bull; <?= e($r['food_type']) ?>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge badge-pink"><?= e($r['category_name']) ?></span>
                    </td>
                    <td>
                      <span style="font-weight: 600;"><?= format_rupiah($r['price_min']) ?> - <?= format_rupiah($r['price_max']) ?></span>
                      <div style="font-size: 0.75rem; color: var(--admin-text-muted);"><?= e($r['price_range']) ?></div>
                    </td>
                    <td>
                      <span style="font-weight: 700; color: #D97706;">
                        <i class="fas fa-star text-warning"></i> <?= number_format((float)$r['rating'], 1) ?>
                      </span>
                      <div style="font-size: 0.75rem; color: var(--admin-text-muted);">
                        (<?= (int)$r['total_reviews'] ?> ulasan)
                      </div>
                    </td>
                    <td>
                      <span class="badge <?= ($r['status'] === 'published') ? 'badge-status-open' : 'badge-status-closed' ?>">
                        <?= ucfirst($r['status']) ?>
                      </span>
                      <?php if ($r['is_popular']): ?>
                        <span class="badge badge-atmosphere" style="font-size: 0.7rem;">Populer</span>
                      <?php endif; ?>
                    </td>
                    <td style="text-align: right;">
                      <div class="action-btns" style="justify-content: flex-end;">
                        <a href="<?= BASE_URL ?>admin/restaurant-edit.php?id=<?= (int)$r['id'] ?>" class="btn-table-action btn-table-edit" title="Edit">
                          <i class="fas fa-pen"></i>
                        </a>
                        <button 
                          type="button" 
                          class="btn-table-action btn-table-delete btn-confirm-delete" 
                          data-action="<?= BASE_URL ?>admin/restaurants.php" 
                          data-id="<?= (int)$r['id'] ?>" 
                          data-name="<?= e($r['name']) ?>" 
                          title="Hapus"
                        >
                          <i class="fas fa-trash-can"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" style="text-align: center; padding: 40px; color: var(--admin-text-muted);">
                    Tidak ada tempat makan yang ditemukan.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>

  <!-- Modal Konfirmasi Hapus yang Aman -->
  <div class="modal-overlay" id="deleteConfirmModal">
    <div class="modal-box">
      <div class="modal-icon-danger">
        <i class="fas fa-triangle-exclamation"></i>
      </div>
      <h3 style="margin-bottom: 8px;">Konfirmasi Penghapusan</h3>
      <p style="color: var(--admin-text-muted); font-size: 0.92rem; margin-bottom: 24px;">
        Apakah Anda yakin ingin menghapus <strong id="deleteItemName" style="color: var(--danger);">item ini</strong>? Tindakan ini tidak dapat dibatalkan.
      </p>
      
      <form action="<?= BASE_URL ?>admin/restaurants.php" method="POST" id="deleteConfirmForm">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="restaurant_id" id="modalRestaurantId" value="">

        <div style="display: flex; gap: 12px; justify-content: center;">
          <button type="button" class="btn btn-outline" id="cancelDeleteModalBtn">Batal</button>
          <button type="submit" class="btn btn-primary" style="background: var(--danger); border-color: var(--danger);">
            Ya, Hapus Sekarang
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="<?= BASE_URL ?>assets/js/script.js"></script>
  <script>
    document.querySelectorAll('.btn-confirm-delete').forEach(btn => {
      btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');
        document.getElementById('modalRestaurantId').value = id;
        document.getElementById('deleteItemName').textContent = name;
        document.getElementById('deleteConfirmModal').classList.add('active');
      });
    });
  </script>
</body>
</html>
