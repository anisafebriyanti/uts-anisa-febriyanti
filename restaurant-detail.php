<?php
/**
 * FoodFinder - Halaman Detail Tempat Makan
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$restaurantId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($restaurantId <= 0) {
    header('Location: ' . BASE_URL . 'search.php');
    exit;
}

// Ambil data restoran dan kategorinya
$stmt = $pdo->prepare("
    SELECT r.*, c.name as category_name, c.icon as category_icon 
    FROM restaurants r 
    JOIN categories c ON r.category_id = c.id 
    WHERE r.id = ? AND r.status = 'published'
");
$stmt->execute([$restaurantId]);
$resto = $stmt->fetch();

if (!$resto) {
    set_flash('danger', 'Tempat makan tidak ditemukan atau belum dipublikasikan.');
    header('Location: ' . BASE_URL . 'search.php');
    exit;
}

$userId = is_logged_in() ? (int)$_SESSION['user']['id'] : 0;
$isFav = is_restaurant_favorite($pdo, $userId, $restaurantId);

// Handle Submit Ulasan Baru
$reviewError = '';
$reviewSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!is_logged_in()) {
        set_flash('warning', 'Silakan masuk terlebih dahulu untuk menulis ulasan.');
        header("Location: " . BASE_URL . "login.php?redirect=" . urlencode("restaurant-detail.php?id=" . $restaurantId));
        exit;
    }

    $ratingInput = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $commentInput = trim($_POST['comment'] ?? '');

    if ($ratingInput < 1 || $ratingInput > 5) {
        $reviewError = 'Harap pilih rating bintang (1 sampai 5).';
    } elseif (empty($commentInput)) {
        $reviewError = 'Harap tuliskan pengalaman atau ulasan Anda.';
    } else {
        // Cek apakah user sudah pernah mengulas restoran ini
        $checkExisting = $pdo->prepare("SELECT id FROM reviews WHERE restaurant_id = ? AND user_id = ?");
        $checkExisting->execute([$restaurantId, $userId]);
        $existing = $checkExisting->fetch();

        if ($existing) {
            // Update ulasan yang sudah ada
            $updateReview = $pdo->prepare("UPDATE reviews SET rating = ?, comment = ?, created_at = NOW() WHERE id = ?");
            $updateReview->execute([$ratingInput, $commentInput, $existing['id']]);
            $reviewSuccess = 'Ulasan Anda berhasil diperbarui!';
        } else {
            // Masukkan ulasan baru
            $insertReview = $pdo->prepare("INSERT INTO reviews (restaurant_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
            $insertReview->execute([$restaurantId, $userId, $ratingInput, $commentInput]);
            $reviewSuccess = 'Terima kasih! Ulasan Anda berhasil dikirim.';
        }

        // Update rating rata-rata restoran di tabel restaurants
        update_restaurant_rating_stats($pdo, $restaurantId);

        // Ambil data terbaru restoran
        $stmt->execute([$restaurantId]);
        $resto = $stmt->fetch();
    }
}

// Ambil Menu Andalan
$menuStmt = $pdo->prepare("SELECT * FROM menus WHERE restaurant_id = ? ORDER BY is_featured DESC, price ASC");
$menuStmt->execute([$restaurantId]);
$menus = $menuStmt->fetchAll();

// Ambil Ulasan-ulasan
$reviewsStmt = $pdo->prepare("
    SELECT rev.*, u.name as user_name, u.avatar as user_avatar 
    FROM reviews rev 
    JOIN users u ON rev.user_id = u.id 
    WHERE rev.restaurant_id = ? 
    ORDER BY rev.created_at DESC
");
$reviewsStmt->execute([$restaurantId]);
$reviews = $reviewsStmt->fetchAll();

// Olah Fasilitas (comma-separated string)
$facilitiesList = [];
if (!empty($resto['facilities'])) {
    $facilitiesList = array_map('trim', explode(',', $resto['facilities']));
}

$pageTitle = $resto['name'];
$pageDesc = substr(strip_tags($resto['description']), 0, 150);
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 30px; padding-bottom: 70px;">
  
  <!-- Breadcrumb Navigasi -->
  <nav aria-label="breadcrumb" style="margin-bottom: 24px; font-size: 0.9rem;">
    <a href="<?= BASE_URL ?>index.php" style="color: var(--text-secondary);">Beranda</a>
    <span style="margin: 0 8px; color: var(--text-muted);"><i class="fas fa-chevron-right" style="font-size: 0.75rem;"></i></span>
    <a href="<?= BASE_URL ?>search.php" style="color: var(--text-secondary);">Jelajahi</a>
    <span style="margin: 0 8px; color: var(--text-muted);"><i class="fas fa-chevron-right" style="font-size: 0.75rem;"></i></span>
    <span style="color: var(--pink-primary); font-weight: 600;"><?= e($resto['name']) ?></span>
  </nav>

  <!-- ==================== HEADER DETAIL RESTORAN ==================== -->
  <div class="detail-header-card">
    <div class="detail-gallery-grid">
      <div class="detail-main-img">
        <img src="<?= get_restaurant_image($resto['image']) ?>" alt="<?= e($resto['name']) ?>">
      </div>
      <div class="detail-side-imgs">
        <div class="detail-side-img-box">
          <img src="<?= BASE_URL ?>assets/images/hero-food.jpg" alt="Foto Suasana">
        </div>
        <div class="detail-side-img-box">
          <img src="<?= get_restaurant_image($resto['image']) ?>" alt="Foto Menu" style="filter: brightness(0.92);">
        </div>
      </div>
    </div>

    <div class="detail-header-info">
      <div class="detail-title-group">
        <div class="detail-meta-pills">
          <span class="badge badge-pink">
            <i class="fas <?= e($resto['category_icon']) ?>"></i> <?= e($resto['category_name']) ?>
          </span>
          <span class="badge badge-atmosphere">
            <i class="fas fa-couch"></i> Suasana <?= e($resto['atmosphere'] ?? 'Santai') ?>
          </span>
          <span class="badge badge-price">
            <?= e($resto['price_range']) ?> (<?= format_rupiah($resto['price_min']) ?> - <?= format_rupiah($resto['price_max']) ?>)
          </span>
          <span class="badge badge-status-open">
            <i class="fas fa-circle" style="font-size: 0.55rem;"></i> Buka Sekarang
          </span>
        </div>

        <h1><?= e($resto['name']) ?></h1>
        
        <p style="color: var(--text-secondary); font-size: 1.05rem; margin-bottom: 12px;">
          <i class="fas fa-utensils text-pink" style="margin-right: 6px;"></i> <?= e($resto['food_type']) ?>
        </p>

        <div style="display: flex; align-items: center; gap: 12px;">
          <div class="star-rating" style="font-size: 1.15rem;">
            <?= render_rating_stars((float)$resto['rating'], true) ?>
          </div>
          <span style="color: var(--text-secondary); font-size: 0.95rem;">
            berdasarkan <strong><?= (int)$resto['total_reviews'] ?> ulasan pengunjung</strong>
          </span>
        </div>
      </div>

      <!-- Tombol Aksi Kanan -->
      <div class="detail-action-buttons">
        <button 
          class="btn-fav btn-fav-toggle <?= $isFav ? 'active' : '' ?>" 
          data-id="<?= (int)$resto['id'] ?>" 
          style="width: 48px; height: 48px; font-size: 1.3rem;"
          title="<?= $isFav ? 'Hapus dari favorit' : 'Tambah ke favorit' ?>"
        >
          <i class="<?= $isFav ? 'fas fa-heart text-danger' : 'far fa-heart' ?>"></i>
        </button>

        <?php if (!empty($resto['gmaps_url'])): ?>
          <a href="<?= e($resto['gmaps_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline">
            <i class="fas fa-map-location-dot"></i> Buka Google Maps
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ==================== LAYOUT KONTEN UTAMA + SIDEBAR ==================== -->
  <div class="detail-content-layout">
    
    <!-- Kolom Kiri: Deskripsi, Menu Andalan, Fasilitas, & Ulasan -->
    <div>
      
      <!-- Panel Deskripsi -->
      <div class="detail-card-panel">
        <h3><i class="fas fa-circle-info text-pink"></i> Tentang Tempat Makan</h3>
        <p style="color: var(--text-secondary); line-height: 1.8; font-size: 1rem;">
          <?= nl2br(e($resto['description'])) ?>
        </p>
      </div>

      <!-- Panel Fasilitas -->
      <?php if (!empty($facilitiesList)): ?>
        <div class="detail-card-panel">
          <h3><i class="fas fa-concierge-bell text-pink"></i> Fasilitas yang Tersedia</h3>
          <div class="facility-pills-list">
            <?php foreach ($facilitiesList as $fac): ?>
              <div class="facility-pill">
                <i class="fas fa-check-circle" style="color: var(--pink-primary);"></i>
                <span><?= e($fac) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Panel Menu Andalan -->
      <div class="detail-card-panel">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
          <h3 style="margin: 0;"><i class="fas fa-book-open text-pink"></i> Menu Andalan</h3>
          <span style="font-size: 0.85rem; color: var(--text-muted);"><?= count($menus) ?> Menu Tersedia</span>
        </div>

        <?php if (count($menus) > 0): ?>
          <div class="menu-grid">
            <?php foreach ($menus as $m): ?>
              <div class="menu-card">
                <div class="menu-name"><?= e($m['name']) ?></div>
                <div class="menu-price"><?= format_rupiah($m['price']) ?></div>
                <?php if (!empty($m['description'])): ?>
                  <div class="menu-desc"><?= e($m['description']) ?></div>
                <?php endif; ?>
                <?php if ($m['is_featured']): ?>
                  <span class="badge badge-pink" style="margin-top: 10px; font-size: 0.72rem;">
                    <i class="fas fa-crown"></i> Signature Menu
                  </span>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p style="color: var(--text-muted); font-style: italic;">
            Belum ada daftar menu andalan yang ditambahkan untuk tempat makan ini.
          </p>
        <?php endif; ?>
      </div>

      <!-- Panel Ulasan Pengguna -->
      <div class="detail-card-panel" id="reviewsSection">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
          <h3 style="margin: 0;"><i class="fas fa-comments text-pink"></i> Ulasan Pengunjung</h3>
          <div class="star-rating">
            <?= render_rating_stars((float)$resto['rating'], true) ?>
          </div>
        </div>

        <!-- Notifikasi Ulasan -->
        <?php if (!empty($reviewError)): ?>
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= e($reviewError) ?>
          </div>
        <?php endif; ?>
        <?php if (!empty($reviewSuccess)): ?>
          <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= e($reviewSuccess) ?>
          </div>
        <?php endif; ?>

        <!-- Formulir Tulis Ulasan -->
        <div style="background: var(--pink-subtle); border: 1px solid var(--pink-border); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 30px;">
          <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 12px; color: var(--text-primary);">
            Bagikan Pengalaman Kuliner Anda
          </h4>

          <?php if (is_logged_in()): ?>
            <form action="<?= BASE_URL ?>restaurant-detail.php?id=<?= $restaurantId ?>#reviewsSection" method="POST">
              <div class="form-group">
                <label style="font-weight: 600; margin-bottom: 6px; display: block;">Beri Rating Bintang:</label>
                <div class="star-rating-input">
                  <input type="radio" id="star5" name="rating" value="5" required>
                  <label for="star5" title="5 Bintang - Luar Biasa"><i class="fas fa-star"></i></label>
                  <input type="radio" id="star4" name="rating" value="4">
                  <label for="star4" title="4 Bintang - Sangat Baik"><i class="fas fa-star"></i></label>
                  <input type="radio" id="star3" name="rating" value="3">
                  <label for="star3" title="3 Bintang - Cukup"><i class="fas fa-star"></i></label>
                  <input type="radio" id="star2" name="rating" value="2">
                  <label for="star2" title="2 Bintang - Kurang"><i class="fas fa-star"></i></label>
                  <input type="radio" id="star1" name="rating" value="1">
                  <label for="star1" title="1 Bintang - Buruk"><i class="fas fa-star"></i></label>
                </div>
                <span id="selectedRatingText" style="font-size: 0.88rem; color: var(--pink-dark); font-weight: 600; margin-left: 10px;"></span>
              </div>

              <div class="form-group">
                <label for="reviewComment">Ulasan Anda:</label>
                <textarea 
                  name="comment" 
                  id="reviewComment" 
                  rows="3" 
                  class="form-control" 
                  placeholder="Ceritakan tentang rasa makanan, suasana tempat, keramahan pelayanan, atau tips menu andalan..." 
                  required
                ></textarea>
              </div>

              <button type="submit" name="submit_review" class="btn btn-primary btn-sm">
                <i class="fas fa-paper-plane"></i> Kirim Ulasan
              </button>
            </form>
          <?php else: ?>
            <p style="color: var(--text-secondary); font-size: 0.92rem; margin-bottom: 16px;">
              Ingin memberikan ulasan dan rating untuk tempat makan ini? Silakan masuk terlebih dahulu dengan akun Anda.
            </p>
            <a href="<?= BASE_URL ?>login.php?redirect=<?= urlencode('restaurant-detail.php?id=' . $restaurantId) ?>" class="btn btn-outline btn-sm">
              <i class="fas fa-right-to-bracket"></i> Masuk untuk Menulis Ulasan
            </a>
          <?php endif; ?>
        </div>

        <!-- Daftar Ulasan -->
        <div class="reviews-list">
          <?php if (count($reviews) > 0): ?>
            <?php foreach ($reviews as $rev): ?>
              <div class="review-item">
                <div class="review-user-avatar">
                  <?= strtoupper(substr($rev['user_name'], 0, 1)) ?>
                </div>
                <div class="review-body">
                  <div class="review-author-row">
                    <div>
                      <div class="review-user-name"><?= e($rev['user_name']) ?></div>
                      <div class="star-rating" style="font-size: 0.82rem;">
                        <?= render_rating_stars((float)$rev['rating']) ?>
                      </div>
                    </div>
                    <span class="review-date">
                      <?= date('d M Y, H:i', strtotime($rev['created_at'])) ?> WIB
                    </span>
                  </div>
                  <p class="review-text">
                    <?= nl2br(e($rev['comment'])) ?>
                  </p>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div style="text-align: center; padding: 30px; color: var(--text-secondary);">
              <i class="far fa-comment-dots" style="font-size: 2.2rem; color: var(--pink-border); margin-bottom: 12px; display: block;"></i>
              Belum ada ulasan untuk tempat makan ini. Jadilah orang pertama yang mengulas!
            </div>
          <?php endif; ?>
        </div>

      </div>

    </div>

    <!-- Kolom Kanan: Sidebar Informasi & Lokasi -->
    <aside>
      <div class="sidebar-info-card">
        <h4>Informasi & Lokasi</h4>

        <div class="info-item">
          <div class="info-icon">
            <i class="fas fa-clock"></i>
          </div>
          <div class="info-text-group">
            <label>Jam Operasional</label>
            <span><?= e($resto['opening_hours']) ?></span>
          </div>
        </div>

        <div class="info-item">
          <div class="info-icon">
            <i class="fas fa-coins"></i>
          </div>
          <div class="info-text-group">
            <label>Kisaran Harga</label>
            <span><?= format_rupiah($resto['price_min']) ?> - <?= format_rupiah($resto['price_max']) ?> / orang</span>
          </div>
        </div>

        <div class="info-item">
          <div class="info-icon">
            <i class="fas fa-couch"></i>
          </div>
          <div class="info-text-group">
            <label>Suasana</label>
            <span><?= e($resto['atmosphere'] ?? 'Santai & Nyaman') ?></span>
          </div>
        </div>

        <div class="info-item">
          <div class="info-icon">
            <i class="fas fa-map-pin"></i>
          </div>
          <div class="info-text-group">
            <label>Alamat Lengkap</label>
            <span><?= e($resto['address']) ?>, <?= e($resto['city']) ?></span>
          </div>
        </div>

        <hr style="border: none; border-top: 1px solid var(--pink-border); margin: 20px 0;">

        <?php if (!empty($resto['gmaps_url'])): ?>
          <a href="<?= e($resto['gmaps_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="width: 100%;">
            <i class="fas fa-diamond-turn-right"></i> Petunjuk Arah (Google Maps)
          </a>
        <?php endif; ?>

        <div style="margin-top: 20px; font-size: 0.82rem; color: var(--text-muted); text-align: center;">
          <i class="fas fa-shield-halved"></i> Data tempat makan diverifikasi oleh admin FoodFinder.
        </div>
      </div>
    </aside>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
