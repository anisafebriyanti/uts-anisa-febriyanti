<?php
/**
 * FoodFinder - Halaman Tempat Makan Favorit Pengguna
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Wajibkan login untuk mengakses halaman favorit
require_login('favorites.php');

$userId = (int) $_SESSION['user']['id'];

// Handle Hapus Favorit via POST manual jika JavaScript dinonaktifkan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_favorite_id'])) {
    $restoId = (int) $_POST['remove_favorite_id'];
    $delStmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND restaurant_id = ?");
    $delStmt->execute([$userId, $restoId]);
    set_flash('info', 'Tempat makan telah dihapus dari daftar favorit Anda.');
    header('Location: ' . BASE_URL . 'favorites.php');
    exit;
}

// Ambil semua tempat makan yang difavoritkan oleh user ini
$stmt = $pdo->prepare("
    SELECT r.*, c.name as category_name, fav.created_at as favorited_at 
    FROM favorites fav 
    JOIN restaurants r ON fav.restaurant_id = r.id 
    JOIN categories c ON r.category_id = c.id 
    WHERE fav.user_id = ? AND r.status = 'published' 
    ORDER BY fav.id DESC
");
$stmt->execute([$userId]);
$favoriteList = $stmt->fetchAll();
$totalFavorites = count($favoriteList);

$pageTitle = 'Tempat Makan Favorit Saya';
$pageDesc = 'Daftar tempat makan dan kafe favorit yang telah Anda simpan di akun FoodFinder.';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 40px; padding-bottom: 80px;">
  
  <div class="section-header">
    <div>
      <div class="section-tag"><i class="fas fa-heart text-danger"></i> Koleksi Pribadi</div>
      <h1 class="section-title">Tempat Makan Favorit Saya</h1>
      <p class="section-subtitle">
        Tersimpan <strong><?= $totalFavorites ?></strong> tempat makan favorit di akun Anda
      </p>
    </div>
    <a href="<?= BASE_URL ?>search.php" class="btn btn-outline btn-sm">
      <i class="fas fa-plus"></i> Jelajahi Lebih Banyak Tempat
    </a>
  </div>

  <?php if ($totalFavorites > 0): ?>
    <div class="restaurant-grid">
      <?php foreach ($favoriteList as $resto): ?>
        <div class="restaurant-card favorite-item-card">
          <div class="restaurant-thumb-wrap">
            <img src="<?= get_restaurant_image($resto['image']) ?>" alt="<?= e($resto['name']) ?>" loading="lazy">
            <div class="restaurant-overlay-badges">
              <span class="badge badge-pink">
                <i class="fas fa-tag"></i> <?= e($resto['category_name']) ?>
              </span>
              <!-- Tombol Hapus Favorit AJAX -->
              <button 
                class="btn-fav btn-fav-toggle active" 
                data-id="<?= (int)$resto['id'] ?>" 
                title="Hapus dari favorit"
                aria-label="Hapus Favorit"
              >
                <i class="fas fa-heart text-danger"></i>
              </button>
            </div>
          </div>

          <div class="restaurant-content">
            <div class="restaurant-tags-row">
              <span class="badge badge-atmosphere">
                <i class="fas fa-couch"></i> <?= e($resto['atmosphere'] ?? 'Santai') ?>
              </span>
              <span class="badge badge-price">
                <?= e($resto['price_range']) ?>
              </span>
            </div>

            <h3 class="restaurant-name">
              <a href="<?= BASE_URL ?>restaurant-detail.php?id=<?= (int)$resto['id'] ?>">
                <?= e($resto['name']) ?>
              </a>
            </h3>

            <div class="restaurant-food-type">
              <i class="fas fa-utensils text-muted"></i> <?= e($resto['food_type']) ?>
            </div>

            <div class="restaurant-rating-row">
              <div class="star-rating">
                <?= render_rating_stars((float)$resto['rating'], true) ?>
                <span style="font-size: 0.8rem; color: var(--text-muted); margin-left: 4px;">
                  (<?= (int)$resto['total_reviews'] ?>)
                </span>
              </div>
              <div class="restaurant-price-text">
                <?= format_rupiah($resto['price_min']) ?>
              </div>
            </div>

            <div class="restaurant-location">
              <i class="fas fa-map-marker-alt"></i>
              <span><?= e($resto['address']) ?></span>
            </div>

            <div class="restaurant-footer-actions">
              <a href="<?= BASE_URL ?>restaurant-detail.php?id=<?= (int)$resto['id'] ?>" class="btn btn-primary btn-sm">
                Lihat Detail <i class="fas fa-arrow-right"></i>
              </a>

              <form action="<?= BASE_URL ?>favorites.php" method="POST" style="display: inline;">
                <input type="hidden" name="remove_favorite_id" value="<?= (int)$resto['id'] ?>">
                <button type="submit" class="btn btn-outline btn-icon" title="Hapus dari daftar favorit" onclick="return confirm('Apakah Anda yakin ingin menghapus dari favorit?')">
                  <i class="fas fa-trash-can text-danger"></i>
                </button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <!-- Tampilan Kosong Jika Belum Ada Favorit -->
    <div class="empty-state">
      <div class="empty-state-icon">
        <i class="far fa-heart" style="color: var(--pink-primary);"></i>
      </div>
      <h3>Belum Ada Tempat Makan Favorit</h3>
      <p>
        Kamu belum menyimpan tempat makan apa pun ke daftar favorit. Klik ikon hati pada tempat makan yang kamu sukai untuk menyimpannya di sini!
      </p>
      <a href="<?= BASE_URL ?>search.php" class="btn btn-primary">
        <i class="fas fa-compass"></i> Jelajahi Tempat Makan Sekarang
      </a>
    </div>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
