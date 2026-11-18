<?php
/**
 * FoodFinder - Halaman Sistem Rekomendasi Tempat Makan
 * Algoritma Pencocokan Preferensi Berdasarkan Kategori, Budget, Rating, dan Suasana
 */

$pageTitle = 'Sistem Rekomendasi Tempat Makan Pintar';
$pageDesc = 'Dapatkan rekomendasi kuliner yang disesuaikan dengan preferensi kategori, budget, rating, dan suasana tempat.';
require_once __DIR__ . '/includes/header.php';

// Ambil daftar kategori
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Preferensi Input
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$budgetMax = isset($_GET['budget_max']) ? (int)$_GET['budget_max'] : 50000;
$minRating = isset($_GET['min_rating']) ? (float)$_GET['min_rating'] : 4.0;
$selectedAtmosphere = trim($_GET['atmosphere'] ?? '');
$isRandom = isset($_GET['random']) && $_GET['random'] == '1';
$hasSubmitted = isset($_GET['recommend']) || $isRandom;

$results = [];
$algorithmExplanation = '';

if ($isRandom) {
    // Mode Rekomendasi Acak (Surprise Me)
    $stmt = $pdo->query("
        SELECT r.*, c.name as category_name 
        FROM restaurants r 
        JOIN categories c ON r.category_id = c.id 
        WHERE r.status = 'published' AND r.rating >= 4.5 
        ORDER BY RAND() 
        LIMIT 3
    ");
    $results = $stmt->fetchAll();
    $algorithmExplanation = 'Menampilkan pilihan acak berkualitas tinggi dengan rating minimal 4.5 bintang untuk kejutan kuliner terbaikmu!';
} elseif ($hasSubmitted) {
    // Bangun query pencocokan preferensi
    $sql = "
        SELECT r.*, c.name as category_name,
        (
            (CASE WHEN ? > 0 AND r.category_id = ? THEN 30 ELSE 0 END) +
            (CASE WHEN r.price_min <= ? THEN 30 ELSE 0 END) +
            (CASE WHEN ? != '' AND r.atmosphere = ? THEN 25 ELSE 0 END) +
            (CASE WHEN r.rating >= ? THEN 15 ELSE 0 END)
        ) as match_score
        FROM restaurants r 
        JOIN categories c ON r.category_id = c.id 
        WHERE r.status = 'published'
    ";
    $params = [
        $selectedCategory, $selectedCategory,
        $budgetMax,
        $selectedAtmosphere, $selectedAtmosphere,
        $minRating
    ];

    // Filter minimum syarat kelayakan
    if ($selectedCategory > 0) {
        $sql .= " AND r.category_id = ?";
        $params[] = $selectedCategory;
    }
    if ($budgetMax > 0) {
        $sql .= " AND r.price_min <= ?";
        $params[] = $budgetMax;
    }
    if ($minRating > 0) {
        $sql .= " AND r.rating >= ?";
        $params[] = $minRating;
    }
    if (!empty($selectedAtmosphere)) {
        $sql .= " AND r.atmosphere = ?";
        $params[] = $selectedAtmosphere;
    }

    $sql .= " ORDER BY match_score DESC, r.rating DESC LIMIT 6";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    // Jika filter terlalu ketat dan tidak ada hasil, lakukan fallback pencarian relaksasi
    if (count($results) === 0) {
        $fallbackSql = "
            SELECT r.*, c.name as category_name 
            FROM restaurants r 
            JOIN categories c ON r.category_id = c.id 
            WHERE r.status = 'published' AND r.price_min <= ? 
            ORDER BY r.rating DESC 
            LIMIT 4
        ";
        $fallbackStmt = $pdo->prepare($fallbackSql);
        $fallbackStmt->execute([$budgetMax * 1.3]);
        $results = $fallbackStmt->fetchAll();
        $algorithmExplanation = 'Kriteria Anda cukup spesifik. Berikut kami tampilkan alternatif terbaik dengan budget terdekat.';
    } else {
        $algorithmExplanation = 'Hasil rekomendasi dihitung menggunakan algoritma pencocokan multi-faktor (kesesuaian kategori, batas budget, rating kualitas, dan suasana pilihan).';
    }
}

$userId = is_logged_in() ? (int) $_SESSION['user']['id'] : 0;
?>

<div class="container" style="padding-top: 40px; padding-bottom: 80px;">
  
  <!-- Header Rekomendasi -->
  <div class="recommendation-hero">
    <div class="hero-tag" style="margin-bottom: 12px;">
      <i class="fas fa-wand-magic-sparkles"></i> Asisten Pencocokan Tempat Makan
    </div>
    <h1 class="hero-title" style="font-size: 2.6rem; margin-bottom: 12px;">
      Rekomendasi <span class="highlight">Sesuai Preferensimu</span>
    </h1>
    <p class="hero-desc" style="max-width: 650px; margin: 0 auto 36px;">
      Atur preferensi kuliner, batas budget dompet, suasana yang kamu inginkan, dan sistem FoodFinder akan mencocokkan tempat makan yang paling pas untukmu.
    </p>
  </div>

  <!-- Formulir Preferensi Rekomendasi -->
  <div class="recommendation-card-form">
    <form action="<?= BASE_URL ?>recommendation.php" method="GET">
      <input type="hidden" name="recommend" value="1">

      <div class="form-grid-2">
        <!-- 1. Pilihan Kategori -->
        <div class="form-group">
          <label for="prefCategory"><i class="fas fa-layer-group text-pink"></i> Kategori Makanan:</label>
          <select name="category" id="prefCategory" class="form-control">
            <option value="0">Semua Kategori (Bebas)</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= (int)$cat['id'] ?>" <?= ($selectedCategory === (int)$cat['id']) ? 'selected' : '' ?>>
                <?= e($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- 2. Batas Budget Maksimum -->
        <div class="form-group">
          <label for="prefBudget"><i class="fas fa-wallet text-pink"></i> Budget Maksimal per Orang:</label>
          <select name="budget_max" id="prefBudget" class="form-control">
            <option value="25000" <?= ($budgetMax === 25000) ? 'selected' : '' ?>>Maks. Rp 25.000 (Sangat Ramah Kantong)</option>
            <option value="50000" <?= ($budgetMax === 50000) ? 'selected' : '' ?>>Maks. Rp 50.000 (Standar Mahasiswa)</option>
            <option value="80000" <?= ($budgetMax === 80000) ? 'selected' : '' ?>>Maks. Rp 80.000 (Menengah / Treat Day)</option>
            <option value="150000" <?= ($budgetMax === 150000) ? 'selected' : '' ?>>Maks. Rp 150.000 (Premium / Fine Casual)</option>
          </select>
        </div>

        <!-- 3. Suasana Tempat -->
        <div class="form-group">
          <label for="prefAtmosphere"><i class="fas fa-couch text-pink"></i> Suasana Tempat yang Diinginkan:</label>
          <select name="atmosphere" id="prefAtmosphere" class="form-control">
            <option value="">Bebas / Fleksibel</option>
            <option value="Santai" <?= ($selectedAtmosphere === 'Santai') ? 'selected' : '' ?>>Santai & Hangout Teman</option>
            <option value="Cocok Tugas / WFC" <?= ($selectedAtmosphere === 'Cocok Tugas / WFC') ? 'selected' : '' ?>>Cocok Nugas / WFC (Tenang & Banyak Stopkontak)</option>
            <option value="Keluarga" <?= ($selectedAtmosphere === 'Keluarga') ? 'selected' : '' ?>>Keluarga (Luas & Nyaman)</option>
            <option value="Romantis" <?= ($selectedAtmosphere === 'Romantis') ? 'selected' : '' ?>>Romantis & Aesthetic</option>
          </select>
        </div>

        <!-- 4. Rating Minimum -->
        <div class="form-group">
          <label for="prefRating"><i class="fas fa-star text-warning"></i> Standar Rating Minimal:</label>
          <select name="min_rating" id="prefRating" class="form-control">
            <option value="4.0" <?= ($minRating == 4.0) ? 'selected' : '' ?>>Bintang 4.0 ke atas (Pasti Bagus)</option>
            <option value="4.5" <?= ($minRating == 4.5) ? 'selected' : '' ?>>Bintang 4.5 ke atas (Sangat Direkomendasikan)</option>
            <option value="4.8" <?= ($minRating == 4.8) ? 'selected' : '' ?>>Bintang 4.8 ke atas (Top Level Populer)</option>
          </select>
        </div>
      </div>

      <!-- Tombol Aksi Formulir -->
      <div style="display: flex; gap: 14px; justify-content: center; margin-top: 10px; flex-wrap: wrap;">
        <button type="submit" class="btn btn-primary btn-lg">
          <i class="fas fa-wand-magic-sparkles"></i> Rekomendasikan untuk Saya
        </button>
        <a href="<?= BASE_URL ?>recommendation.php?random=1" class="btn btn-outline btn-lg">
          <i class="fas fa-dice"></i> Rekomendasi Acak (Surprise Me!)
        </a>
      </div>
    </form>
  </div>

  <!-- ==================== HASIL REKOMENDASI ==================== -->
  <?php if ($hasSubmitted): ?>
    <section>
      <div class="section-header">
        <div>
          <div class="section-tag"><i class="fas fa-sparkles"></i> Hasil Pencocokan</div>
          <h2 class="section-title">Tempat Makan yang Paling Cocok</h2>
          <?php if (!empty($algorithmExplanation)): ?>
            <p class="section-subtitle" style="color: var(--pink-dark); font-weight: 500;">
              <i class="fas fa-circle-info"></i> <?= e($algorithmExplanation) ?>
            </p>
          <?php endif; ?>
        </div>
      </div>

      <?php if (count($results) > 0): ?>
        <div class="restaurant-grid">
          <?php foreach ($results as $resto): 
            $isFav = is_restaurant_favorite($pdo, $userId, (int)$resto['id']);
          ?>
            <div class="restaurant-card">
              <div class="restaurant-thumb-wrap">
                <img src="<?= get_restaurant_image($resto['image']) ?>" alt="<?= e($resto['name']) ?>" loading="lazy">
                <div class="restaurant-overlay-badges">
                  <span class="badge badge-pink">
                    <i class="fas fa-tag"></i> <?= e($resto['category_name']) ?>
                  </span>
                  <button 
                    class="btn-fav btn-fav-toggle <?= $isFav ? 'active' : '' ?>" 
                    data-id="<?= (int)$resto['id'] ?>" 
                    title="<?= $isFav ? 'Hapus dari favorit' : 'Tambah ke favorit' ?>"
                    aria-label="Simpan Favorit"
                  >
                    <i class="<?= $isFav ? 'fas fa-heart text-danger' : 'far fa-heart' ?>"></i>
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
                    <?= format_rupiah($resto['price_min']) ?> - <?= format_rupiah($resto['price_max']) ?>
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
                  <?php if (!empty($resto['gmaps_url'])): ?>
                    <a href="<?= e($resto['gmaps_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-icon" title="Buka Google Maps">
                      <i class="fas fa-diamond-turn-right"></i>
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <div class="empty-state-icon">
            <i class="fas fa-face-frown"></i>
          </div>
          <h3>Belum Ada Tempat Makan yang Cocok</h3>
          <p>
            Coba longgarkan budget atau ubah suasana tempat makan untuk menemukan lebih banyak pilihan.
          </p>
        </div>
      <?php endif; ?>
    </section>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
