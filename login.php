<?php
/**
 * FoodFinder - Halaman Masuk (Login)
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Jika sudah login, redirect sesuai role
if (is_logged_in()) {
    if (is_admin()) {
        header('Location: ' . BASE_URL . 'admin/index.php');
    } else {
        header('Location: ' . BASE_URL . 'index.php');
    }
    exit;
}

$error = '';
$redirect = $_GET['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan kata sandi wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login Berhasil - Simpan Session
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int) $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'avatar' => $user['avatar']
            ];

            set_flash('success', 'Selamat datang kembali, ' . e($user['name']) . '! Senang melihatmu lagi.');

            if (!empty($redirect) && !str_starts_with($redirect, 'http')) {
                header('Location: ' . BASE_URL . ltrim($redirect, '/'));
            } else {
                if ($user['role'] === 'admin') {
                    header('Location: ' . BASE_URL . 'admin/index.php');
                } else {
                    header('Location: ' . BASE_URL . 'index.php');
                }
            }
            exit;
        } else {
            $error = 'Email atau kata sandi yang Anda masukkan salah.';
        }
    }
}

$pageTitle = 'Masuk ke Akun Anda';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page-wrapper">
  <div class="auth-card">
    <div class="auth-card-header">
      <div class="brand-icon">
        <i class="fas fa-utensils"></i>
      </div>
      <h1 class="auth-card-title">Selamat Datang</h1>
      <p class="auth-card-subtitle">Masuk untuk menyimpan tempat favorit dan menulis ulasan</p>
    </div>

    <!-- Kotak Demo Otomatis untuk Kemudahan Pengujian Dosen & Mahasiswa -->
    <div class="quick-demo-box">
      <div><strong><i class="fas fa-key"></i> Akun Demo Pengujian Cepat:</strong></div>
      <div class="quick-demo-btns">
        <button type="button" class="btn btn-outline btn-sm" onclick="fillDemoLogin('admin@foodfinder.com', 'admin123')">
          <i class="fas fa-shield"></i> Akun Admin
        </button>
        <button type="button" class="btn btn-outline btn-sm" onclick="fillDemoLogin('user@foodfinder.com', 'user123')">
          <i class="fas fa-user"></i> Akun User
        </button>
      </div>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger">
        <i class="fas fa-circle-exclamation"></i> <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>login.php" method="POST">
      <?php if (!empty($redirect)): ?>
        <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
      <?php endif; ?>

      <div class="form-group">
        <label for="loginEmail"><i class="fas fa-envelope text-pink"></i> Alamat Email:</label>
        <input 
          type="email" 
          name="email" 
          id="loginEmail" 
          class="form-control" 
          placeholder="nama@email.com" 
          value="<?= isset($_POST['email']) ? e($_POST['email']) : '' ?>" 
          required 
          autocomplete="email"
        >
      </div>

      <div class="form-group">
        <label for="loginPassword"><i class="fas fa-lock text-pink"></i> Kata Sandi:</label>
        <input 
          type="password" 
          name="password" 
          id="loginPassword" 
          class="form-control" 
          placeholder="Masukkan kata sandi" 
          required 
          autocomplete="current-password"
        >
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
        <i class="fas fa-right-to-bracket"></i> Masuk Sekarang
      </button>
    </form>

    <div style="text-align: center; margin-top: 24px; font-size: 0.92rem; color: var(--text-secondary);">
      Belum memiliki akun FoodFinder? 
      <a href="<?= BASE_URL ?>register.php" style="font-weight: 700; color: var(--pink-primary);">Daftar di sini</a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
