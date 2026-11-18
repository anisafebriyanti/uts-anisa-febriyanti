<?php
/**
 * FoodFinder Admin - Edit Informasi Tempat Makan
 */

require_once __DIR__ . '/auth_check.php';

$restoId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($restoId <= 0) {
    header('Location: ' . BASE_URL . 'admin/restaurants.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
$stmt->execute([$restoId]);
$resto = $stmt->fetch();

if (!$resto) {
    set_flash('danger', 'Data tempat makan tidak ditemukan.');
    header('Location: ' . BASE_URL . 'admin/restaurants.php');
    exit;
}

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

    // Hitung Price Range tier
    $priceRange = '$$';
    if ($priceMin < 25000) $priceRange = '$';
    elseif ($priceMin >= 25000 && $priceMin < 50000) $priceRange = '$$';
    elseif ($priceMin >= 50000 && $priceMin < 100000) $priceRange = '$$$';
    else $priceRange = '$$$$';

    if (empty($name) || $categoryId <= 0 || empty($foodType) || empty($address) || empty($description)) {
        $error = 'Nama, kategori, jenis makanan, alamat, dan deskripsi wajib diisi.';
    } elseif ($priceMin <= 0 || $priceMax <= 0 || $priceMin > $priceMax) {
        $error = 'Rentang harga tidak valid.';
    } else {
        $imageFilename = $resto['image'];

        // Cek jika ada upload gambar baru
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = handle_image_upload($_FILES['image'], 'uploads/restaurants/');
            if (!$uploadResult['success']) {
                $error = $uploadResult['error'];
            } else {
                $imageFilename = $uploadResult['filename'];
            }
        }

        if (empty($error)) {
            $update = $pdo->prepare("
                UPDATE restaurants SET 
                    category_id = ?, name = ?, food_type = ?, price_range = ?,
                    price_min = ?, price_max = ?, address = ?, city = ?,
                    gmaps_url = ?, opening_hours = ?, description = ?,
                    facilities = ?, atmosphere = ?, image = ?,
                    is_popular = ?, is_recommended = ?, status = ?
                WHERE id = ?
            ");
            $update->execute([
                $categoryId, $name, $foodType, $priceRange,
                $priceMin, $priceMax, $address, $city,
                $gmapsUrl, $openingHours, $description,
                $facilities, $atmosphere, $imageFilename,
                $isPopular, $isRecommended, $status,
                $restoId
            ]);

            set_flash('success', 'Data tempat makan "' . e($name) . '" berhasil diperbarui.');
            header('Location: ' . BASE_URL . 'admin/restaurants.php');
            exit;
        }
    }
}

// Olah Fasilitas yang tersimpan
$savedFacilities = !empty($resto['facilities']) ? array_map('trim', explode(',', $resto['facilities'])) : [];

$pageTitle = 'Edit: ' . $resto['name'];
$pageDesc = 'Perbarui rincian, harga, dan pengaturan tempat makan';
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
          <h3><i class="fas fa-pen-to-square text-pink"></i> Edit Tempat Makan</h3>
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

          <form action="<?= BASE_URL ?>admin/restaurant-edit.php?id=<?= $restoId ?>" method="POST" enctype="multipart/form-data">
            
            <div class="form-grid-2">
              <div class="form-group">
                <label for="restoName">Nama Tempat Makan *</label>
                <input type="text" name="name" id="restoName" class="form-control" value="<?= e($resto['name']) ?>" required>
              </div>

              <div class="form-group">
                <label for="restoCat">Kategori Makanan *</label>
                <select name="category_id" id="restoCat" class="form-control" required>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>" <?= ($resto['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                      <?= e($cat['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-grid-2">
              <div class="form-group">
                <label for="restoFoodType">Jenis Makanan / Menu Utama *</label>
                <input type="text" name="food_type" id="restoFoodType" class="form-control" value="<?= e($resto['food_type']) ?>" required>
              </div>

              <div class="form-group">
                <label for="restoAtmosphere">Suasana Tempat</label>
                <select name="atmosphere" id="restoAtmosphere" class="form-control">
                  <option value="Santai" <?= ($resto['atmosphere'] === 'Santai') ? 'selected' : '' ?>>Santai & Hangout</option>
                  <option value="Cocok Tugas / WFC" <?= ($resto['atmosphere'] === 'Cocok Tugas / WFC') ? 'selected' : '' ?>>Cocok Nugas / WFC</option>
                  <option value="Keluarga" <?= ($resto['atmosphere'] === 'Keluarga') ? 'selected' : '' ?>>Keluarga</option>
                  <option value="Romantis" <?= ($resto['atmosphere'] === 'Romantis') ? 'selected' : '' ?>>Romantis & Aesthetic</option>
                </select>
              </div>
            </div>

            <div class="form-grid-2">
              <div class="form-group">
                <label for="priceMin">Harga Minimum (Rp) *</label>
                <input type="number" name="price_min" id="priceMin" class="form-control" min="1000" step="1000" value="<?= (int)$resto['price_min'] ?>" required>
              </div>

              <div class="form-group">
                <label for="priceMax">Harga Maksimum (Rp) *</label>
                <input type="number" name="price_max" id="priceMax" class="form-control" min="1000" step="1000" value="<?= (int)$resto['price_max'] ?>" required>
              </div>
            </div>

            <div class="form-grid-2">
              <div class="form-group">
                <label for="restoCity">Kota / Wilayah *</label>
                <input type="text" name="city" id="restoCity" class="form-control" value="<?= e($resto['city']) ?>" required>
              </div>

              <div class="form-group">
                <label for="restoHours">Jam Operasional *</label>
                <input type="text" name="opening_hours" id="restoHours" class="form-control" value="<?= e($resto['opening_hours']) ?>" required>
              </div>
            </div>

            <div class="form-group">
              <label for="restoAddress">Alamat Lengkap *</label>
              <textarea name="address" id="restoAddress" rows="2" class="form-control" required><?= e($resto['address']) ?></textarea>
            </div>

            <div class="form-group">
              <label for="gmapsUrl">Tautan Google Maps</label>
              <input type="url" name="gmaps_url" id="gmapsUrl" class="form-control" value="<?= e($resto['gmaps_url'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label for="restoDesc">Deskripsi Tempat Makan *</label>
              <textarea name="description" id="restoDesc" rows="4" class="form-control" required><?= e($resto['description']) ?></textarea>
            </div>

            <!-- Fasilitas Checkboxes -->
            <div class="form-group">
              <label style="display: block; margin-bottom: 8px;">Fasilitas Tersedia:</label>
              <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px;">
                <?php 
                  $commonFacs = ['WiFi Cepat', 'Stopkontak Tiap Meja', 'AC Dingin', 'Mushola', 'Area Merokok', 'Area Outdoor', 'Parkir Mobil', 'Parkir Motor', 'Pembayaran QRIS', 'Kursi Bayi'];
                  foreach ($commonFacs as $cf):
                    $checked = in_array($cf, $savedFacilities) ? 'checked' : '';
                ?>
                  <label class="filter-checkbox-label">
                    <input type="checkbox" name="facilities[]" value="<?= e($cf) ?>" <?= $checked ?>>
                    <span><?= e($cf) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Foto Saat Ini & Upload Baru -->
            <div class="form-group">
              <label style="display: block; margin-bottom: 8px;">Foto Saat Ini:</label>
              <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 12px;">
                <img src="<?= get_restaurant_image($resto['image']) ?>" alt="" style="width: 120px; height: 80px; object-fit: cover; border-radius: 10px; border: 1px solid var(--admin-pink-border);">
                <div>
                  <span style="font-size: 0.88rem; font-weight: 600;"><?= e($resto['image']) ?></span>
                  <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Pilih file baru di bawah ini jika ingin mengganti gambar.</div>
                </div>
              </div>

              <label for="restoImage">Ganti Foto (Opsional, JPG/PNG/WEBP, Max 2MB):</label>
              <input type="file" name="image" id="restoImage" class="form-control" accept="image/jpeg,image/png,image/webp">
            </div>

            <!-- Status & Label -->
            <div class="form-grid-2">
              <div class="form-group">
                <label for="pubStatus">Status Publikasi</label>
                <select name="status" id="pubStatus" class="form-control">
                  <option value="published" <?= ($resto['status'] === 'published') ? 'selected' : '' ?>>Published (Tampil di Website)</option>
                  <option value="draft" <?= ($resto['status'] === 'draft') ? 'selected' : '' ?>>Draft (Sembunyikan)</option>
                </select>
              </div>

              <div style="display: flex; gap: 20px; align-items: center; margin-top: 26px;">
                <label class="filter-checkbox-label">
                  <input type="checkbox" name="is_popular" value="1" <?= ($resto['is_popular'] == 1) ? 'checked' : '' ?>>
                  <span>Tandai sebagai Populer</span>
                </label>
                <label class="filter-checkbox-label">
                  <input type="checkbox" name="is_recommended" value="1" <?= ($resto['is_recommended'] == 1) ? 'checked' : '' ?>>
                  <span>Tandai sebagai Rekomendasi</span>
                </label>
              </div>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 20px;">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Perbarui Data
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
