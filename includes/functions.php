<?php
/**
 * FoodFinder - Helper Functions
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Mencegah XSS dengan htmlspecialchars
 */
function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Format angka ke mata uang Rupiah
 */
function format_rupiah($number): string {
    return 'Rp ' . number_format((float)$number, 0, ',', '.');
}

/**
 * Cek apakah user sedang login
 */
function is_logged_in(): bool {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

/**
 * Cek apakah user sedang login sebagai admin
 */
function is_admin(): bool {
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

/**
 * Ambil data user yang sedang login
 */
function current_user() {
    return $_SESSION['user'] ?? null;
}

/**
 * Wajibkan login untuk mengakses halaman tertentu
 */
function require_login(string $redirect = 'login.php'): void {
    if (!is_logged_in()) {
        set_flash('warning', 'Silakan masuk (login) terlebih dahulu untuk melanjutkan.');
        header('Location: ' . BASE_URL . $redirect);
        exit;
    }
}

/**
 * Wajibkan role admin untuk mengakses area admin
 */
function require_admin(): void {
    if (!is_admin()) {
        set_flash('danger', 'Akses ditolak. Halaman ini hanya dapat diakses oleh Administrator.');
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}

/**
 * Set flash message untuk notifikasi
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

/**
 * Ambil dan bersihkan flash message
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Tampilkan bintang rating visual
 */
function render_rating_stars(float $rating, bool $showNumber = false): string {
    $full = floor($rating);
    $half = ($rating - $full) >= 0.3 && ($rating - $full) <= 0.7 ? 1 : 0;
    if (($rating - $full) > 0.7) {
        $full += 1;
    }
    $empty = 5 - ($full + $half);
    if ($empty < 0) $empty = 0;

    $html = '<div class="star-rating" title="Rating: ' . number_format($rating, 1) . ' / 5">';
    for ($i = 0; $i < $full; $i++) {
        $html .= '<i class="fas fa-star text-warning"></i>';
    }
    for ($i = 0; $i < $half; $i++) {
        $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
    }
    for ($i = 0; $i < $empty; $i++) {
        $html .= '<i class="far fa-star text-muted"></i>';
    }
    if ($showNumber) {
        $html .= ' <span class="rating-number">' . number_format($rating, 1) . '</span>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * Mendapatkan URL gambar restoran dengan fallback jika tidak ditemukan
 */
function get_restaurant_image(string $imageName = null): string {
    if (empty($imageName)) {
        return BASE_URL . 'assets/images/hero-food.jpg';
    }
    // Jika berupa URL lengkap
    if (str_starts_with($imageName, 'http://') || str_starts_with($imageName, 'https://')) {
        return $imageName;
    }
    // Cek di folder uploads/restaurants/
    $uploadPath = __DIR__ . '/../uploads/restaurants/' . $imageName;
    if (file_exists($uploadPath)) {
        return BASE_URL . 'uploads/restaurants/' . $imageName;
    }
    // Cek di folder assets/images/
    $assetPath = __DIR__ . '/../assets/images/' . $imageName;
    if (file_exists($assetPath)) {
        return BASE_URL . 'assets/images/' . $imageName;
    }
    return BASE_URL . 'assets/images/hero-food.jpg';
}

/**
 * Buat string slug URL ramah SEO
 */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

/**
 * Cek apakah sebuah restoran sudah difavoritkan oleh user
 */
function is_restaurant_favorite(PDO $pdo, int $userId, int $restaurantId): bool {
    if ($userId <= 0) return false;
    $stmt = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND restaurant_id = ?");
    $stmt->execute([$userId, $restaurantId]);
    return (bool) $stmt->fetchColumn();
}

/**
 * Hitung ulang rating rata-rata & total ulasan sebuah restoran
 */
function update_restaurant_rating_stats(PDO $pdo, int $restaurantId): void {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total, COALESCE(AVG(rating), 0) as avg_rating 
        FROM reviews 
        WHERE restaurant_id = ?
    ");
    $stmt->execute([$restaurantId]);
    $stats = $stmt->fetch();

    $totalReviews = (int) ($stats['total'] ?? 0);
    $avgRating = round((float) ($stats['avg_rating'] ?? 0), 1);

    $update = $pdo->prepare("
        UPDATE restaurants 
        SET rating = ?, total_reviews = ? 
        WHERE id = ?
    ");
    $update->execute([$avgRating, $totalReviews, $restaurantId]);
}

/**
 * Helper validasi dan upload gambar yang aman
 * Mendukung JPG, PNG, WEBP, ukuran max 2MB
 */
function handle_image_upload(array $file, string $targetFolder = 'uploads/restaurants/'): array {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'error' => 'Parameter upload tidak valid.'];
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'error' => 'Tidak ada file yang dipilih.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Gagal mengunggah file (Kode error: ' . $file['error'] . ').'];
    }

    // Batasan ukuran 2MB (2 * 1024 * 1024 bytes)
    if ($file['size'] > 2097152) {
        return ['success' => false, 'error' => 'Ukuran file melebihi batas maksimal 2MB.'];
    }

    // Cek MIME type dengan aman
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $allowedMimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp'
    ];

    $ext = array_search($mimeType, $allowedMimes, true);
    if ($ext === false) {
        return ['success' => false, 'error' => 'Format file tidak didukung. Harap unggah format JPG, PNG, atau WEBP.'];
    }

    // Buat nama acak yang unik dan aman
    $filename = bin2hex(random_bytes(10)) . '.' . $ext;
    $destination = __DIR__ . '/../' . rtrim($targetFolder, '/') . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'Gagal menyimpan file ke server.'];
    }

    return ['success' => true, 'filename' => $filename];
}
