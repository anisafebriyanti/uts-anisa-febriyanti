<?php
/**
 * FoodFinder Admin - Dashboard Utama
 */

require_once __DIR__ . '/auth_check.php';

// Ambil Statistik
$totalResto = (int) $pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn();
$totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCats = (int) $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalReviews = (int) $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();

// Ambil 5 Tempat Makan Rating Tertinggi
$topRestos = $pdo->query("
    SELECT r.*, c.name as category_name 
    FROM restaurants r 
    JOIN categories c ON r.category_id = c.id 
    ORDER BY r.rating DESC, r.total_reviews DESC 
    LIMIT 5
")->fetchAll();

// Ambil 5 Ulasan Terbaru
$latestReviews = $pdo->query("
    SELECT rev.*, u.name as user_name, r.name as restaurant_name 
    FROM reviews rev 
    JOIN users u ON rev.user_id = u.id 
    JOIN restaurants r ON rev.restaurant_id = r.id 
    ORDER BY rev.created_at DESC 
    LIMIT 5
")->fetchAll();

$pageTitle = 'Dashboard Ringkasan';
$pageDesc = 'Selamat datang di Panel Manajemen FoodFinder';
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

  <!-- Main Area -->
  <div class="admin-main">
    <?php include __DIR__ . '/topbar.php'; ?>

    <main class="admin-content">
      
      <!-- Kartu Metrik Statistik -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-info">
            <span>Total Tempat Makan</span>
            <h3><?= $totalResto ?></h3>
          </div>
          <div class="stat-icon pink">
            <i class="fas fa-store"></i>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <span>Total Pengguna</span>
            <h3><?= $totalUsers ?></h3>
          </div>
          <div class="stat-icon blue">
            <i class="fas fa-users"></i>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <span>Kategori Makanan</span>
            <h3><?= $totalCats ?></h3>
          </div>
          <div class="stat-icon purple">
            <i class="fas fa-layer-group"></i>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <span>Total Ulasan</span>
            <h3><?= $totalReviews ?></h3>
          </div>
          <div class="stat-icon green">
            <i class="fas fa-comments"></i>
          </div>
        </div>
      </div>

      <!-- Grid 2 Kolom: Tempat Makan Rating Tertinggi & Ulasan Terbaru -->
      <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 28px;">
        
        <!-- Panel 1: Rating Tertinggi -->
        <div class="admin-panel">
          <div class="admin-panel-header">
            <h3><i class="fas fa-crown text-warning"></i> Tempat Makan Rating Tertinggi</h3>
            <a href="<?= BASE_URL ?>admin/restaurants.php" class="btn btn-outline btn-sm">Kelola Semua</a>
          </div>

          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Tempat Makan</th>
                  <th>Kategori</th>
                  <th>Rating</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($topRestos as $r): ?>
                  <tr>
                    <td>
                      <div style="display: flex; align-items: center; gap: 12px;">
                        <img src="<?= get_restaurant_image($r['image']) ?>" alt="" class="table-img-thumb">
                        <div>
                          <strong><?= e($r['name']) ?></strong>
                          <div style="font-size: 0.78rem; color: var(--admin-text-muted);"><?= e($r['city']) ?></div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge badge-pink"><?= e($r['category_name']) ?></span>
                    </td>
                    <td>
                      <span style="font-weight: 700; color: #D97706;">
                        <i class="fas fa-star text-warning"></i> <?= number_format((float)$r['rating'], 1) ?>
                      </span>
                      <small style="color: var(--admin-text-muted);"> (<?= (int)$r['total_reviews'] ?>)</small>
                    </td>
                    <td>
                      <span class="badge <?= ($r['status'] === 'published') ? 'badge-status-open' : 'badge-status-closed' ?>">
                        <?= ucfirst($r['status']) ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Panel 2: Ulasan Pengunjung Terbaru -->
        <div class="admin-panel">
          <div class="admin-panel-header">
            <h3><i class="fas fa-comments text-pink"></i> Ulasan Pengunjung Terbaru</h3>
            <a href="<?= BASE_URL ?>admin/reviews.php" class="btn btn-outline btn-sm">Moderasi</a>
          </div>

          <div style="padding: 18px 24px;">
            <?php if (count($latestReviews) > 0): ?>
              <?php foreach ($latestReviews as $rev): ?>
                <div style="padding: 14px 0; border-bottom: 1px solid #FDF0F5;">
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <strong style="font-size: 0.92rem; color: var(--admin-text-main);">
                      <?= e($rev['user_name']) ?> 
                      <span style="font-weight: 400; color: var(--admin-text-muted);">di</span> 
                      <span style="color: var(--admin-pink);"><?= e($rev['restaurant_name']) ?></span>
                    </strong>
                    <span style="font-size: 0.82rem; color: #D97706; font-weight: 700;">
                      <i class="fas fa-star text-warning"></i> <?= (int)$rev['rating'] ?>
                    </span>
                  </div>
                  <p style="margin: 0; font-size: 0.86rem; color: var(--admin-text-muted); line-height: 1.5;">
                    "<?= e(substr($rev['comment'], 0, 100)) ?><?= (strlen($rev['comment']) > 100) ? '...' : '' ?>"
                  </p>
                  <small style="font-size: 0.75rem; color: #B3A4AC;">
                    <?= date('d M Y, H:i', strtotime($rev['created_at'])) ?> WIB
                  </small>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p style="color: var(--admin-text-muted); text-align: center; padding: 20px;">
                Belum ada ulasan yang masuk.
              </p>
            <?php endif; ?>
          </div>
        </div>

      </div>

    </main>
  </div>

  <script src="<?= BASE_URL ?>assets/js/script.js"></script>
</body>
</html>
