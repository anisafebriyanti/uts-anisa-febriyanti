<?php
/**
 * FoodFinder Admin - Manajemen & Moderasi Ulasan
 */

require_once __DIR__ . '/auth_check.php';

// Handle Hapus / Moderasi Ulasan (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_review') {
    $reviewId = (int) ($_POST['review_id'] ?? 0);
    if ($reviewId > 0) {
        $find = $pdo->prepare("SELECT restaurant_id FROM reviews WHERE id = ?");
        $find->execute([$reviewId]);
        $rev = $find->fetch();

        if ($rev) {
            $restaurantId = (int) $rev['restaurant_id'];

            // Hapus ulasan
            $del = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
            $del->execute([$reviewId]);

            // Sinkronisasi rating & total review restoran
            update_restaurant_rating_stats($pdo, $restaurantId);

            set_flash('success', 'Ulasan berhasil dihapus dan rating restoran telah diperbarui secara otomatis.');
        } else {
            set_flash('danger', 'Ulasan tidak ditemukan.');
        }
    }
    header('Location: ' . BASE_URL . 'admin/reviews.php');
    exit;
}

// Ambil semua ulasan
$reviews = $pdo->query("
    SELECT rev.*, u.name as user_name, u.email as user_email,
           r.name as restaurant_name, r.id as resto_id 
    FROM reviews rev 
    JOIN users u ON rev.user_id = u.id 
    JOIN restaurants r ON rev.restaurant_id = r.id 
    ORDER BY rev.created_at DESC
")->fetchAll();

$pageTitle = 'Moderasi Ulasan Pengunjung';
$pageDesc = 'Pantau dan bersihkan ulasan yang tidak pantas atau berindikasi spam';
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
      <div class="admin-panel">
        <div class="admin-panel-header">
          <h3><i class="fas fa-comments text-pink"></i> Semua Ulasan Pengunjung (<?= count($reviews) ?>)</h3>
        </div>

        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Pengulas</th>
                <th>Tempat Makan</th>
                <th>Rating</th>
                <th style="width: 40%;">Isi Komentar</th>
                <th>Tanggal</th>
                <th style="text-align: right;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $rev): ?>
                  <tr>
                    <td>
                      <strong><?= e($rev['user_name']) ?></strong>
                      <div style="font-size: 0.78rem; color: var(--admin-text-muted);"><?= e($rev['user_email']) ?></div>
                    </td>
                    <td>
                      <a href="<?= BASE_URL ?>restaurant-detail.php?id=<?= (int)$rev['resto_id'] ?>" target="_blank" style="color: var(--admin-pink); font-weight: 600;">
                        <?= e($rev['restaurant_name']) ?>
                      </a>
                    </td>
                    <td>
                      <span style="font-weight: 700; color: #D97706;">
                        <i class="fas fa-star text-warning"></i> <?= (int)$rev['rating'] ?> / 5
                      </span>
                    </td>
                    <td>
                      <div style="font-size: 0.9rem; line-height: 1.5; color: var(--admin-text-main);">
                        "<?= e($rev['comment']) ?>"
                      </div>
                    </td>
                    <td style="color: var(--admin-text-muted); font-size: 0.82rem; white-space: nowrap;">
                      <?= date('d M Y, H:i', strtotime($rev['created_at'])) ?>
                    </td>
                    <td style="text-align: right;">
                      <form action="<?= BASE_URL ?>admin/reviews.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ulasan ini? Rating restoran akan dihitung ulang secara otomatis.')">
                        <input type="hidden" name="action" value="delete_review">
                        <input type="hidden" name="review_id" value="<?= (int)$rev['id'] ?>">
                        <button type="submit" class="btn-table-action btn-table-delete" title="Hapus Ulasan Ini">
                          <i class="fas fa-trash-can"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" style="text-align: center; padding: 40px; color: var(--admin-text-muted);">
                    Belum ada ulasan yang masuk.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <script src="<?= BASE_URL ?>assets/js/script.js"></script>
</body>
</html>
