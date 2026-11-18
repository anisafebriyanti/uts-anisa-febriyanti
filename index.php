<?php
/**
 * FoodFinder - Halaman Beranda (Index)
 */

$pageTitle = 'Temukan Tempat Makan & Kafe Favoritmu';
$pageDesc = 'Cari dan dapatkan rekomendasi tempat makan, kafe WFC, dan kuliner lezat sesuai selera dan budgetmu.';
require_once __DIR__ . '/includes/header.php';

// Ambil kategori makanan
$categoriesStmt = $pdo->query("
    SELECT c.*, COUNT(r.id) as total_resto 
    FROM categories c 
    LEFT JOIN restaurants r ON c.id = r.category_id AND r.status = 'published'
    GROUP BY c.id 
    ORDER BY c.id ASC
");
$categories = $categoriesStmt->fetchAll();

// Ambil tempat makan Populer (is_popular = 1)
$popularStmt = $pdo->query("
    SELECT r.*, c.name as category_name 
    FROM restaurants r 
    JOIN categories c ON r.category_id = c.id 
    WHERE r.status = 'published' AND r.is_popular = 1 
    ORDER BY r.rating DESC 
    LIMIT 6
");
$popularRestaurants = $popularStmt->fetchAll();

// Ambil Rekomendasi Pilihan FoodFinder (is_recommended = 1)
$recommendedStmt = $pdo->query("
    SELECT r.*, c.name as category_name 
    FROM restaurants r 
    JOIN categories c ON r.category_id = c.id 
    WHERE r.status = 'published' AND r.is_recommended = 1 
    ORDER BY r.id DESC 
    LIMIT 6
");
$recommendedRestaurants = $recommendedStmt->fetchAll();

// Ambil Tempat Makan Rating Tertinggi (Top Rated)
$topRatedStmt = $pdo->query("
    SELECT r.*, c.name as category_name 
    FROM restaurants r 
    JOIN categories c ON r.category_id = c.id 
    WHERE r.status = 'published' 
    ORDER BY r.rating DESC, r.total_reviews DESC 
    LIMIT 6
");
$topRatedRestaurants = $topRatedStmt->fetchAll();

$userId = is_logged_in() ? (int) $_SESSION['user']['id'] : 0;
?>

<!-- ============================================================
     HERO SECTION
============================================================ -->
<section class="hero-section">
  <div class="hero-bg-accent"></div>
  <div class="hero-bg-accent-2"></div>
  
  <div class="container">
    <div class="hero-grid">
      <!-- Teks & Formulir Pencarian Hero -->
      <div class="hero-content">
        <div class="hero-tag">
          <i class="fas fa-sparkles"></i> Panduan Kuliner Terlengkap & Terpercaya
        </div>
        
        <h1 class="hero-title">
          Lagi Pengin Makan <br>
          <span class="highlight">Apa Hari Ini?</span>
        </h1>
        
        <p class="hero-desc">
          FoodFinder membantu mahasiswa dan pencinta kuliner menemukan kafe nyaman untuk nugas, makanan tradisional lezat, hingga kuliner viral ramah kantong.
        </p>

        <!-- Formulir Pencarian Cepat -->
        <form action="<?= BASE_URL ?>search.php" method="GET" class="hero-search-card">
          <i class="fas fa-search search-icon"></i>
          <input 
            type="text" 
            name="q" 
            placeholder="Cari nama resto, jenis makanan (cth: ramen, ayam geprek, kopi)..." 
            autocomplete="off"
            required
          >
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-arrow-right"></i> Cari Tempat
          </button>
        </form>

        <!-- Tag Pencarian Populer -->
        <div class="quick-tags">
          <span class="quick-tags-label"><i class="fas fa-fire text-danger"></i> Populer:</span>
          <a href="<?= BASE_URL ?>search.php?q=kopi" class="quick-tag-chip">Kafe WFC</a>
          <a href="<?= BASE_URL ?>search.php?q=geprek" class="quick-tag-chip">Ayam Geprek</a>
          <a href="<?= BASE_URL ?>search.php?q=ramen" class="quick-tag-chip">Ramen</a>
          <a href="<?= BASE_URL ?>search.php?q=gelato" class="quick-tag-chip">Gelato</a>
          <a href="<?= BASE_URL ?>search.php?q=bebek" class="quick-tag-chip">Bebek Goreng</a>
        </div>
      </div>

      <!-- Banner Visual Hero -->
      <div class="hero-banner-wrapper">
        <div class="hero-main-card">
          <img src="<?= BASE_URL ?>assets/images/hero-food.jpg" alt="Makanan Lezat FoodFinder">
          
          <div class="hero-floating-badge">
            <div class="floating-icon-wrap">
              <i class="fas fa-heart text-danger"></i>
            </div>
            <div>
              <div class="floating-badge-title">100+ Rekomendasi</div>
              <div class="floating-badge-subtitle">Pilihan Tempat Makan Teruji</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     KATEGORI MAKANAN
============================================================ -->
<section class="category-section">
  <div class="container">
    <div class="section-header">
      <div>
        <div class="section-tag"><i class="fas fa-layer-group"></i> Kategori Pilihan</div>
        <h2 class="section-title">Eksplorasi Berdasarkan Kategori</h2>
        <p class="section-subtitle">Pilih selera makanmu dari aneka pilihan kategori lezat kami</p>
      </div>
      <a href="<?= BASE_URL ?>search.php" class="btn btn-outline btn-sm">
        Lihat Semua <i class="fas fa-arrow-right"></i>
      </a>
    </div>

    <div class="category-grid">
      <?php foreach ($categories as $cat): ?>
        <a href="<?= BASE_URL ?>search.php?category=<?= (int)$cat['id'] ?>" class="category-card">
          <div class="category-icon-box">
            <i class="fas <?= e($cat['icon']) ?>"></i>
          </div>
          <span class="category-name"><?= e($cat['name']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============================================================
     TEMPAT MAKAN POPULER
============================================================ -->
<section style="padding: 60px 0;">
  <div class="container">
    <div class="section-header">
      <div>
        <div class="section-tag"><i class="fas fa-fire"></i> Paling Diminati</div>
        <h2 class="section-title">Tempat Makan Populer</h2>
        <p class="section-subtitle">Tempat makan yang sedang ramai dikunjungi dan dicari pengguna</p>
      </div>
      <a href="<?= BASE_URL ?>search.php?filter=popular" class="btn btn-outline btn-sm">
        Lihat Lebih Banyak <i class="fas fa-arrow-right"></i>
      </a>
    </div>

    <div class="restaurant-grid">
      <?php foreach ($popularRestaurants as $resto): 
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
                <i class="fas fa-mug-hot"></i> <?= e($resto['atmosphere'] ?? 'Santai') ?>
              </span>
              <span class="badge badge-price">
                <?= e($resto['price_range']) ?> (<?= format_rupiah($resto['price_min']) ?>)
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
                  (<?= (int)$resto['total_reviews'] ?> ulasan)
                </span>
              </div>
              <div class="restaurant-price-text">
                <?= format_rupiah($resto['price_min']) ?>+
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
                <a href="<?= e($resto['gmaps_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-icon" title="Buka di Google Maps">
                  <i class="fas fa-diamond-turn-right"></i>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============================================================
     FITUR BANNER (CARA KERJA FOODFINDER)
============================================================ -->
<section class="how-it-works-section">
  <div class="container">
    <div style="text-align: center; max-width: 600px; margin: 0 auto 48px;">
      <div class="section-tag"><i class="fas fa-magic"></i> Mengapa FoodFinder?</div>
      <h2 class="section-title">Temukan Makanan Terbaik Tanpa Bingung</h2>
      <p class="section-subtitle">Kami memudahkan pencarian kuliner dengan pencocokan pintar dan informasi detail</p>
    </div>

    <div class="features-grid">
      <div class="feature-box">
        <div class="feature-box-icon">
          <i class="fas fa-filter"></i>
        </div>
        <h4>Filter Kategori & Budget</h4>
        <p>Saring tempat makan berdasarkan budget mahasiswa, suasana tenang untuk nugas, hingga restoran keluarga.</p>
      </div>

      <div class="feature-box">
        <div class="feature-box-icon">
          <i class="fas fa-wand-magic-sparkles"></i>
        </div>
        <h4>Rekomendasi Cerdas</h4>
        <p>Sistem pencocokan preferensi membantu menyarankan tempat makan yang paling pas dengan suasana hati dan kantongmu.</p>
      </div>

      <div class="feature-box">
        <div class="feature-box-icon">
          <i class="fas fa-heart"></i>
        </div>
        <h4>Simpan ke Favorit</h4>
        <p>Tandai tempat makan incaranmu dengan akun pribadi dan kunjungi kembali kapan saja dengan mudah.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     REKOMENDASI TEMPAT MAKAN
============================================================ -->
<section style="padding: 70px 0;">
  <div class="container">
    <div class="section-header">
      <div>
        <div class="section-tag"><i class="fas fa-thumbs-up"></i> Rekomendasi Pilihan</div>
        <h2 class="section-title">Rekomendasi Tempat Makan</h2>
        <p class="section-subtitle">Tempat makan pilihan kurator kami dengan cita rasa luar biasa</p>
      </div>
      <a href="<?= BASE_URL ?>recommendation.php" class="btn btn-outline btn-sm">
        Coba Rekomendasi Pintar <i class="fas fa-wand-sparkles"></i>
      </a>
    </div>

    <div class="restaurant-grid">
      <?php foreach ($recommendedRestaurants as $resto): 
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
                <i class="fas fa-mug-hot"></i> <?= e($resto['atmosphere'] ?? 'Santai') ?>
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
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============================================================
     TEMPAT MAKAN DENGAN RATING TERTINGGI
============================================================ -->
<section style="padding: 30px 0 80px;">
  <div class="container">
    <div class="section-header">
      <div>
        <div class="section-tag"><i class="fas fa-star text-warning"></i> Terbaik dari Pengguna</div>
        <h2 class="section-title">Tempat Makan Rating Tertinggi</h2>
        <p class="section-subtitle">Tempat makan yang mendapatkan bintang dan ulasan paling memuaskan</p>
      </div>
      <a href="<?= BASE_URL ?>search.php?sort=rating_desc" class="btn btn-outline btn-sm">
        Urutkan Rating <i class="fas fa-star text-warning"></i>
      </a>
    </div>

    <div class="restaurant-grid">
      <?php foreach ($topRatedRestaurants as $resto): 
        $isFav = is_restaurant_favorite($pdo, $userId, (int)$resto['id']);
      ?>
        <div class="restaurant-card">
          <div class="restaurant-thumb-wrap">
            <img src="<?= get_restaurant_image($resto['image']) ?>" alt="<?= e($resto['name']) ?>" loading="lazy">
            <div class="restaurant-overlay-badges">
              <span class="badge" style="background: rgba(255, 184, 0, 0.95); color: #fff; font-weight: 700;">
                <i class="fas fa-crown"></i> <?= number_format((float)$resto['rating'], 1) ?>
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
              <span class="badge badge-pink">
                <i class="fas fa-tag"></i> <?= e($resto['category_name']) ?>
              </span>
              <span class="badge badge-atmosphere">
                <?= e($resto['atmosphere'] ?? 'Santai') ?>
              </span>
            </div>

            <h3 class="restaurant-name">
              <a href="<?= BASE_URL ?>restaurant-detail.php?id=<?= (int)$resto['id'] ?>">
                <?= e($resto['name']) ?>
              </a>
            </h3>

            <div class="restaurant-food-type">
              <i class="fas fa-clock text-muted"></i> <?= e($resto['opening_hours']) ?>
            </div>

            <div class="restaurant-rating-row">
              <div class="star-rating">
                <?= render_rating_stars((float)$resto['rating'], true) ?>
                <span style="font-size: 0.8rem; color: var(--text-muted); margin-left: 4px;">
                  (<?= (int)$resto['total_reviews'] ?> ulasan)
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
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
