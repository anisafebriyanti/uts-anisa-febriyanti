<?php
/**
 * FoodFinder - Halaman Pencarian & Jelajahi Tempat Makan
 */

$pageTitle = 'Pencarian & Jelajahi Tempat Makan';
$pageDesc = 'Cari tempat makan favorit berdasarkan nama, jenis makanan, kategori, harga, suasana, dan rating.';
require_once __DIR__ . '/includes/header.php';

// Ambil semua kategori untuk filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Parameter Pencarian & Filter dari GET
$keyword = trim($_GET['q'] ?? '');
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$selectedPrice = trim($_GET['price'] ?? '');
$selectedAtmosphere = trim($_GET['atmosphere'] ?? '');
$minRating = isset($_GET['rating']) ? (float)$_GET['rating'] : 0;
$sortBy = trim($_GET['sort'] ?? 'rating_desc');
$filterSpecial = trim($_GET['filter'] ?? '');

// Bangun Query Pencarian Dinamis dengan Prepared Statement
$sql = "
    SELECT r.*, c.name as category_name 
    FROM restaurants r 
    JOIN categories c ON r.category_id = c.id 
    WHERE r.status = 'published'
";
$params = [];

// Filter Kata Kunci (Nama resto, jenis makanan, deskripsi, atau kategori)
if (!empty($keyword)) {
    $sql .= " AND (r.name LIKE ? OR r.food_type LIKE ? OR r.description LIKE ? OR c.name LIKE ?)";
    $like = "%{$keyword}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

// Filter Kategori
if ($selectedCategory > 0) {
    $sql .= " AND r.category_id = ?";
    $params[] = $selectedCategory;
}

// Filter Kisaran Harga
if ($selectedPrice === 'under_25k') {
    $sql .= " AND r.price_min <= 25000";
} elseif ($selectedPrice === '25k_50k') {
    $sql .= " AND ((r.price_min BETWEEN 25000 AND 50000) OR (r.price_max BETWEEN 25000 AND 50000))";
} elseif ($selectedPrice === '50k_100k') {
    $sql .= " AND ((r.price_min BETWEEN 50000 AND 100000) OR (r.price_max BETWEEN 50000 AND 100000))";
} elseif ($selectedPrice === 'above_100k') {
    $sql .= " AND r.price_max >= 100000";
}

// Filter Suasana
if (!empty($selectedAtmosphere)) {
    $sql .= " AND r.atmosphere = ?";
    $params[] = $selectedAtmosphere;
}

// Filter Rating Minimum
if ($minRating > 0) {
    $sql .= " AND r.rating >= ?";
    $params[] = $minRating;
}

// Filter Khusus (Populer)
if ($filterSpecial === 'popular') {
    $sql .= " AND r.is_popular = 1";
} elseif ($filterSpecial === 'recommended') {
    $sql .= " AND r.is_recommended = 1";
}

// Pengurutan (Sorting)
switch ($sortBy) {
    case 'price_asc':
        $sql .= " ORDER BY r.price_min ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY r.price_min DESC";
        break;
    case 'newest':
        $sql .= " ORDER BY r.id DESC";
        break;
    case 'rating_desc':
    default:
        $sql .= " ORDER BY r.rating DESC, r.total_reviews DESC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();
$totalResults = count($results);

$userId = is_logged_in() ? (int) $_SESSION['user']['id'] : 0;
?>

<div class="container">
  <!-- Bar Pencarian Cepat Atas -->
  <div style="padding-top: 36px; padding-bottom: 10px;">
    <form action="<?= BASE_URL ?>search.php" method="GET" class="hero-search-card" style="box-shadow: var(--shadow-sm); margin-bottom: 10px;">
      <i class="fas fa-search search-icon"></i>
      <input 
        type="text" 
        name="q" 
        value="<?= e($keyword) ?>" 
        placeholder="Cari nama tempat makan, jenis makanan, menu, atau kategori..."
      >
      <!-- Pertahankan parameter filter jika ada -->
      <?php if ($selectedCategory > 0): ?>
        <input type="hidden" name="category" value="<?= $selectedCategory ?>">
      <?php endif; ?>
      <?php if (!empty($selectedPrice)): ?>
        <input type="hidden" name="price" value="<?= e($selectedPrice) ?>">
      <?php endif; ?>
      <?php if (!empty($selectedAtmosphere)): ?>
        <input type="hidden" name="atmosphere" value="<?= e($selectedAtmosphere) ?>">
      <?php endif; ?>
      <button type="submit" class="btn btn-primary">
        Cari
      </button>
    </form>
  </div>

  <!-- Layout Halaman Pencarian: Sidebar Filter + Grid Hasil -->
  <div class="search-page-layout">
    
    <!-- ==================== SIDEBAR FILTER ==================== -->
    <aside class="filter-sidebar">
      <div class="filter-sidebar-header">
        <h3><i class="fas fa-sliders text-pink"></i> Filter Pencarian</h3>
        <a href="<?= BASE_URL ?>search.php" style="font-size: 0.85rem; color: var(--pink-primary); font-weight: 600;">
          Reset
        </a>
      </div>

      <form action="<?= BASE_URL ?>search.php" method="GET" id="filterForm">
        <?php if (!empty($keyword)): ?>
          <input type="hidden" name="q" value="<?= e($keyword) ?>">
        <?php endif; ?>

        <!-- Filter Kategori -->
        <div class="filter-group">
          <label class="filter-label"><i class="fas fa-layer-group text-pink"></i> Kategori Makanan</label>
          <div class="filter-options">
            <label class="filter-checkbox-label">
              <input type="radio" name="category" value="0" <?= ($selectedCategory === 0) ? 'checked' : '' ?> onchange="this.form.submit()">
              <span>Semua Kategori</span>
            </label>
            <?php foreach ($categories as $cat): ?>
              <label class="filter-checkbox-label">
                <input 
                  type="radio" 
                  name="category" 
                  value="<?= (int)$cat['id'] ?>" 
                  <?= ($selectedCategory === (int)$cat['id']) ? 'checked' : '' ?> 
                  onchange="this.form.submit()"
                >
                <span><?= e($cat['name']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Filter Kisaran Budget / Harga -->
        <div class="filter-group">
          <label class="filter-label"><i class="fas fa-wallet text-pink"></i> Kisaran Budget</label>
          <div class="filter-options">
            <label class="filter-checkbox-label">
              <input type="radio" name="price" value="" <?= empty($selectedPrice) ? 'checked' : '' ?> onchange="this.form.submit()">
              <span>Semua Harga</span>
            </label>
            <label class="filter-checkbox-label">
              <input type="radio" name="price" value="under_25k" <?= ($selectedPrice === 'under_25k') ? 'checked' : '' ?> onchange="this.form.submit()">
              <span>&lt; Rp 25.000 (Hemat Pelajar)</span>
            </label>
            <label class="filter-checkbox-label">
              <input type="radio" name="price" value="25k_50k" <?= ($selectedPrice === '25k_50k') ? 'checked' : '' ?> onchange="this.form.submit()">
              <span>Rp 25.000 - Rp 50.000</span>
            </label>
            <label class="filter-checkbox-label">
              <input type="radio" name="price" value="50k_100k" <?= ($selectedPrice === '50k_100k') ? 'checked' : '' ?> onchange="this.form.submit()">
              <span>Rp 50.000 - Rp 100.000</span>
            </label>
            <label class="filter-checkbox-label">
              <input type="radio" name="price" value="above_100k" <?= ($selectedPrice === 'above_100k') ? 'checked' : '' ?> onchange="this.form.submit()">
              <span>&gt; Rp 100.000 (Premium)</span>
            </label>
          </div>
        </div>

        <!-- Filter Suasana Tempat -->
        <div class="filter-group">
          <label class="filter-label"><i class="fas fa-couch text-pink"></i> Suasana Tempat</label>
          <div class="filter-options">
            <label class="filter-checkbox-label">
              <input type="radio" name="atmosphere" value="" <?= empty($selectedAtmosphere) ? 'checked' : '' ?> onchange="this.form.submit()">
              <span>Semua Suasana</span>
            </label>
            <label class="filter-checkbox-label">
              <input type="radio" name="atmosphere" value="Santai" <?= ($selectedAtmosphere === 'Santai') ? 'checked' : '' ?> onchange="this.form.submit()">
              <span>Santai & Nongkrong</span>
            </label>
            <label class="filter-checkbox-label">
              <input type="radio" name="atmosphere" value="Cocok Tugas / WFC" <?= ($selectedAtmosphere === 'Cocok Tugas / WFC') ? 'checked' : '' ?> onchange="this.form.submit()">
              <span>Cocok Nugas / WFC</span>
            </label>
            <label class="filter-checkbox-label">
              <input type="radio" name="atmosphere" value="Keluarga" <?= ($selectedAtmosphere === 'Keluarga') ? 'checked' : '' ?> onchange="this.form.submit()">
              <span>Keluarga</span>
            </label>
            <label class="filter-checkbox-label">
              <input type="radio" name="atmosphere" value="Romantis" <?= ($selectedAtmosphere === 'Romantis') ? 'checked' : '' ?> onchange="this.form.submit()">
              <span>Romantis & Aesthetic</span>
            </label>
          </div>
        </div>

        <!-- Filter Rating Minimum -->
        <div class="filter-group">
          <label class="filter-label"><i class="fas fa-star text-warning"></i> Rating Minimum</label>
          <div class="filter-options">
            <label class="filter-checkbox-label">
              <input type="radio" name="rating" value="0" <?= ($minRating == 0) ? 'checked' : '' ?> onchange="this.form.submit()">
              <span>Semua Rating</span>
            </label>
            <label class="filter-checkbox-label">
              <input type="radio" name="rating" value="4.5" <?= ($minRating == 4.5) ? 'checked' : '' ?> onchange="this.form.submit()">
              <span><i class="fas fa-star text-warning"></i> 4.5 ke atas</span>
            </label>
            <label class="filter-checkbox-label">
              <input type="radio" name="rating" value="4.0" <?= ($minRating == 4.0) ? 'checked' : '' ?> onchange="this.form.submit()">
              <span><i class="fas fa-star text-warning"></i> 4.0 ke atas</span>
            </label>
          </div>
        </div>
      </form>
    </aside>

    <!-- ==================== AREA HASIL PENCARIAN ==================== -->
    <main>
      <div class="search-results-header">
        <div>
          <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 4px;">
            <?php if (!empty($keyword)): ?>
              Hasil untuk "<?= e($keyword) ?>"
            <?php else: ?>
              Daftar Tempat Makan
            <?php endif; ?>
          </h2>
          <p style="color: var(--text-secondary); font-size: 0.92rem;">
            Menampilkan <strong><?= $totalResults ?></strong> tempat makan yang sesuai
          </p>
        </div>

        <!-- Pengurutan (Sort Dropdown) -->
        <div class="sort-select-wrap">
          <label for="sortSelect" style="font-size: 0.88rem; font-weight: 600; color: var(--text-secondary);">
            Urutkan:
          </label>
          <select id="sortSelect" class="custom-select" onchange="window.location.href = this.value">
            <?php
              $baseUrlQuery = 'search.php?' . http_build_query(array_merge($_GET, ['sort' => 'PLACEHOLDER']));
              $makeSortUrl = function($sortKey) use ($baseUrlQuery) {
                  return BASE_URL . str_replace('PLACEHOLDER', $sortKey, $baseUrlQuery);
              };
            ?>
            <option value="<?= $makeSortUrl('rating_desc') ?>" <?= ($sortBy === 'rating_desc') ? 'selected' : '' ?>>
              Rating Tertinggi ⭐
            </option>
            <option value="<?= $makeSortUrl('price_asc') ?>" <?= ($sortBy === 'price_asc') ? 'selected' : '' ?>>
              Harga Termurah 💰
            </option>
            <option value="<?= $makeSortUrl('price_desc') ?>" <?= ($sortBy === 'price_desc') ? 'selected' : '' ?>>
              Harga Termahal 💎
            </option>
            <option value="<?= $makeSortUrl('newest') ?>" <?= ($sortBy === 'newest') ? 'selected' : '' ?>>
              Terbaru 🕒
            </option>
          </select>
        </div>
      </div>

      <!-- Hasil Grid Tempat Makan -->
      <?php if ($totalResults > 0): ?>
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
                  <span><?= e($resto['address']) ?>, <?= e($resto['city']) ?></span>
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
        <!-- Tampilan Kosong Jika Tidak Ada Hasil -->
        <div class="empty-state">
          <div class="empty-state-icon">
            <i class="fas fa-magnifying-glass"></i>
          </div>
          <h3>Tidak Ada Tempat Makan Ditemukan</h3>
          <p>
            Maaf, kami tidak menemukan tempat makan yang sesuai dengan kriteria filter atau kata kunci Anda. Silakan coba kata kunci lain atau reset filter.
          </p>
          <a href="<?= BASE_URL ?>search.php" class="btn btn-primary">
            <i class="fas fa-rotate-left"></i> Reset Semua Filter
          </a>
        </div>
      <?php endif; ?>
    </main>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
