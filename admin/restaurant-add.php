<?php
/**
 * FoodFinder Admin - Tambah Tempat Makan Baru
 */

require_once __DIR__ . '/auth_check.php';

$error = '';
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $foodType = trim($_POST['food_type'] ?? '');
    $priceMin = (int) ($_POST['price_min'] ?? 0);
    $priceMax = (int) ($_POST['price_max'] ?? 0);
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? 'Jakarta');
    $gmapsUrl = trim($_POST['gmaps_url'] ?? '');
    $openingHours = trim($_POST['opening_hours'] ?? '10:00 - 22:00 WIB');
    $atmosphere = trim($_POST['atmosphere'] ?? 'Santai');
    $facilities = isset($_POST['facilities']) && is_array($_POST['facilities']) ? implode(', ', $_POST['facilities']) : '';
    $description = trim($_POST['description'] ?? '');
    $isPopular = isset($_POST['is_popular']) ? 1 : 0;
    $isRecommended = isset($_POST['is_recommended']) ? 1 : 0;
    $status = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';

    // Menu andalan perdana (opsional)
    $menuName = trim($_POST['menu_name'] ?? '');
    $menuPrice = (int) ($_POST['menu_price'] ?? 0);

    // Hitung Price Range tier
    $priceRange = '$$';
    if ($priceMin < 25000) $priceRange = '$';
    elseif ($priceMin >= 25000 && $priceMin < 50000) $priceRange = '$$';
    elseif ($priceMin >= 50000 && $priceMin < 100000) $priceRange = '$$$';
    else $priceRange = '$$$$';

    if (empty($name) || $categoryId <= 0 || empty($foodType) || empty($address) || empty($description)) {
        $error = 'Nama, kategori, jenis makanan, alamat, dan deskripsi wajib diisi.';
    } elseif ($priceMin <= 0 || $priceMax <= 0 || $priceMin > $priceMax) {
        $error = 'Rentang harga tidak valid (Harga minimal harus lebih kecil dari harga maksimal).';
    } else {
        // Upload Gambar
        $imageFilename = 'resto-kafe.jpg'; // fallback default
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = handle_image_upload($_FILES['image'], 'uploads/restaurants/');
            if (!$uploadResult['success']) {
                $error = $uploadResult['error'];
            } else {
                $imageFilename = $uploadResult['filename'];
            }
        }

        if (empty($error)) {
            $slug = slugify($name);
            // Pastikan slug unik
            $slugCheck = $pdo->prepare("SELECT id FROM restaurants WHERE slug = ?");
            $slugCheck->execute([$slug]);
            if ($slugCheck->fetch()) {
                $slug .= '-' . rand(100, 999);
            }

            // Simpan ke database
            $insert = $pdo->prepare("
                INSERT INTO restaurants (
                    category_id, name, slug, food_type, price_range, price_min, price_max,
                    rating, total_reviews, address, city, gmaps_url, opening_hours,
                    description, facilities, atmosphere, image, is_popular, is_recommended, status, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?,
                    5.0, 1, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, NOW()
                )
            ");
            $insert->execute([
                $categoryId, $name, $slug, $foodType, $priceRange, $priceMin, $priceMax,
                $address, $city, $gmapsUrl, $openingHours,
                $description, $facilities, $atmosphere, $imageFilename, $isPopular, $isRecommended, $status
            ]);
            $newRestoId = (int) $pdo->lastInsertId();

            // Tambahkan menu andalan jika diisi
            if (!empty($menuName) && $menuPrice > 0) {
                $insMenu = $pdo->prepare("INSERT INTO menus (restaurant_id, name, price, is_featured) VALUES (?, ?, ?, 1)");
                $insMenu->execute([$newRestoId, $menuName, $menuPrice]);
            }

            set_flash('success', 'Tempat makan baru "' . e($name) . '" berhasil ditambahkan.');
            header('Location: ' . BASE_URL . 'admin/restaurants.php');
            exit;
        }
    }
}

$pageTitle = 'Tambah Tempat Makan Baru';
$pageDesc = 'Formulir input data restoran, kafe, atau warung makan baru';
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
      <div class="admin-panel" style="max-width: 900px; margin: 0 auto;">
        <div class="admin-panel-header">
          <h3><i class="fas fa-plus-circle text-pink"></i> Formulir Tempat Makan Baru</h3>
          <a href="<?= BASE_URL ?>admin/restaurants.php" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        </div>

        <div style="padding: 30px;">
          <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
              <i class="fas fa-circle-exclamation"></i> <?= e($error) ?>
            </div>
          <?php endif; ?>

          <form action="<?= BASE_URL ?>admin/restaurant-add.php" method="POST" enctype="multipart/form-data">
            
            <div class="form-grid-2">
              <div class="form-group">
                <label for="restoName">Nama Tempat Makan *</label>
                <input type="text" name="name" id="restoName" class="form-control" placeholder="Contoh: Kopi Kenangan Senja" required value="<?= isset($_POST['name']) ? e($_POST['name']) : '' ?>">
              </div>

              <div class="form-group">
                <label for="restoCat">Kategori Makanan *</label>
                <select name="category_id" id="restoCat" class="form-control" required>
                  <option value="">-- Pilih Kategori --</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                      <?= e($cat['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-grid-2">
              <div class="form-group">
                <label for="restoFoodType">Jenis Makanan / Menu Utama *</label>
                <input type="text" name="food_type" id="restoFoodType" class="form-control" placeholder="Contoh: Kopi Spesialti, Pastry & Toast" required value="<?= isset($_POST['food_type']) ? e($_POST['food_type']) : '' ?>">
              </div>

              <div class="form-group">
                <label for="restoAtmosphere">Suasana Tempat</label>
                <select name="atmosphere" id="restoAtmosphere" class="form-control">
                  <option value="Santai">Santai & Hangout</option>
                  <option value="Cocok Tugas / WFC">Cocok Nugas / WFC</option>
                  <option value="Keluarga">Keluarga</option>
                  <option value="Romantis">Romantis & Aesthetic</option>
                </select>
              </div>
            </div>

            <div class="form-grid-2">
              <div class="form-group">
                <label for="priceMin">Harga Minimum (Rp) *</label>
                <input type="number" name="price_min" id="priceMin" class="form-control" placeholder="Contoh: 15000" min="1000" step="1000" required value="<?= isset($_POST['price_min']) ? e($_POST['price_min']) : '20000' ?>">
              </div>

              <div class="form-group">
                <label for="priceMax">Harga Maksimum (Rp) *</label>
                <input type="number" name="price_max" id="priceMax" class="form-control" placeholder="Contoh: 50000" min="1000" step="1000" required value="<?= isset($_POST['price_max']) ? e($_POST['price_max']) : '60000' ?>">
              </div>
            </div>

            <div class="form-grid-2">
              <div class="form-group">
                <label for="restoCity">Kota / Wilayah *</label>
                <input type="text" name="city" id="restoCity" class="form-control" placeholder="Contoh: Jakarta Selatan" required value="<?= isset($_POST['city']) ? e($_POST['city']) : 'Jakarta Selatan' ?>">
              </div>

              <div class="form-group">
                <label for="restoHours">Jam Operasional *</label>
                <input type="text" name="opening_hours" id="restoHours" class="form-control" placeholder="Contoh: 09:00 - 22:00 WIB" required value="<?= isset($_POST['opening_hours']) ? e($_POST['opening_hours']) : '10:00 - 22:00 WIB' ?>">
              </div>
            </div>

            <div class="form-group">
              <label for="restoAddress">Alamat Lengkap *</label>
              <textarea name="address" id="restoAddress" rows="2" class="form-control" placeholder="Jalan, nomor, kelurahan, kecamatan..." required><?= isset($_POST['address']) ? e($_POST['address']) : '' ?></textarea>
            </div>

            <div class="form-group">
              <label for="gmapsUrl">Tautan Google Maps</label>
              <input type="url" name="gmaps_url" id="gmapsUrl" class="form-control" placeholder="https://maps.google.com/?q=..." value="<?= isset($_POST['gmaps_url']) ? e($_POST['gmaps_url']) : '' ?>">
            </div>

            <div class="form-group">
              <label for="restoDesc">Deskripsi Tempat Makan *</label>
              <textarea name="description" id="restoDesc" rows="4" class="form-control" placeholder="Jelaskan keunggulan rasa, konsep tempat, target pengunjung..." required><?= isset($_POST['description']) ? e($_POST['description']) : '' ?></textarea>
            </div>

            <!-- Fasilitas Checkboxes -->
            <div class="form-group">
              <label style="display: block; margin-bottom: 8px;">Fasilitas Tersedia:</label>
              <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px;">
                <?php 
                  $commonFacs = ['WiFi Cepat', 'Stopkontak Tiap Meja', 'AC Dingin', 'Mushola', 'Area Merokok', 'Area Outdoor', 'Parkir Mobil', 'Parkir Motor', 'Pembayaran QRIS', 'Kursi Bayi'];
                  foreach ($commonFacs as $cf):
                ?>
                  <label class="filter-checkbox-label">
                    <input type="checkbox" name="facilities[]" value="<?= e($cf) ?>">
                    <span><?= e($cf) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Upload Foto -->
            <div class="form-group">
              <label for="restoImage">Unggah Foto Tempat Makan (JPG/PNG/WEBP, Max 2MB)</label>
              <input type="file" name="image" id="restoImage" class="form-control" accept="image/jpeg,image/png,image/webp">
              <small style="color: var(--admin-text-muted);">Jika tidak diunggah, akan menggunakan foto ilustrasi default.</small>
            </div>

            <!-- Menu Andalan Perdana -->
            <div style="background: var(--admin-pink-light); border: 1px dashed var(--admin-pink); border-radius: var(--radius-md); padding: 18px; margin-bottom: 24px;">
              <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--admin-pink-dark); margin-bottom: 10px;">
                <i class="fas fa-utensils"></i> Tambahkan Satu Menu Andalan (Opsional)
              </h4>
              <div class="form-grid-2">
                <div>
                  <label style="font-size: 0.85rem;">Nama Menu:</label>
                  <input type="text" name="menu_name" class="form-control" placeholder="Contoh: Es Kopi Susu Aren">
                </div>
                <div>
                  <label style="font-size: 0.85rem;">Harga Menu (Rp):</label>
                  <input type="number" name="menu_price" class="form-control" placeholder="Contoh: 22000" min="0" step="500">
                </div>
              </div>
            </div>

            <!-- Pengaturan Status & Label -->
            <div class="form-grid-2">
              <div class="form-group">
                <label for="pubStatus">Status Publikasi</label>
                <select name="status" id="pubStatus" class="form-control">
                  <option value="published">Published (Tampil di Website)</option>
                  <option value="draft">Draft (Simpan sebagai Draf)</option>
                </select>
              </div>

              <div style="display: flex; gap: 20px; align-items: center; margin-top: 26px;">
                <label class="filter-checkbox-label">
                  <input type="checkbox" name="is_popular" value="1">
                  <span>Tandai sebagai Populer</span>
                </label>
                <label class="filter-checkbox-label">
                  <input type="checkbox" name="is_recommended" value="1">
                  <span>Tandai sebagai Rekomendasi</span>
                </label>
              </div>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 20px;">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Tempat Makan
              </button>
              <a href="<?= BASE_URL ?>admin/restaurants.php" class="btn btn-outline">Batal</a>
            </div>

          </form>
        </div>
      </div>
    </main>
  </div>

  <script src="<?= BASE_URL ?>assets/js/script.js"></script>
</body>
</html>
