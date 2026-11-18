<?php
/**
 * FoodFinder - Halaman Pendaftaran (Register)
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Jika sudah login, redirect ke beranda
if (is_logged_in()) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$error = '';
$nameVal = '';
$emailVal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    $nameVal = $name;
    $emailVal = $email;

    if (empty($name) || empty($email) || empty($password) || empty($passwordConfirm)) {
        $error = 'Semua bidang formulir wajib diisi.';
    } elseif (strlen($name) < 3) {
        $error = 'Nama lengkap minimal 3 karakter.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format alamat email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Kata sandi minimal 6 karakter demi keamanan akun Anda.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Konfirmasi kata sandi tidak cocok.';
    } else {
        // Cek apakah email sudah terdaftar
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            $error = 'Alamat email ini sudah terdaftar. Silakan gunakan email lain atau masuk.';
        } else {
            // Hash password dan simpan user baru
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $insertStmt = $pdo->prepare("
                INSERT INTO users (name, email, password, role, created_at) 
                VALUES (?, ?, ?, 'user', NOW())
            ");
            $insertStmt->execute([$name, $email, $hashedPassword]);
            $newUserId = (int) $pdo->lastInsertId();

            // Auto-login setelah registrasi berhasil
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => $newUserId,
                'name' => $name,
                'email' => $email,
                'role' => 'user',
                'avatar' => null
            ];

            set_flash('success', 'Akun berhasil dibuat! Selamat datang di FoodFinder, ' . e($name) . '.');
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }
    }
}

$pageTitle = 'Daftar Akun Baru';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page-wrapper">
  <div class="auth-card">
    <div class="auth-card-header">
      <div class="brand-icon">
        <i class="fas fa-user-plus"></i>
      </div>
      <h1 class="auth-card-title">Buat Akun Baru</h1>
      <p class="auth-card-subtitle">Bergabung bersama komunitas pencinta kuliner FoodFinder</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger">
        <i class="fas fa-circle-exclamation"></i> <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>register.php" method="POST">
      <div class="form-group">
        <label for="regName"><i class="fas fa-user text-pink"></i> Nama Lengkap:</label>
        <input 
          type="text" 
          name="name" 
          id="regName" 
          class="form-control" 
          placeholder="Contoh: Anisa Putri" 
          value="<?= e($nameVal) ?>" 
          required
        >
      </div>

      <div class="form-group">
        <label for="regEmail"><i class="fas fa-envelope text-pink"></i> Alamat Email:</label>
        <input 
          type="email" 
          name="email" 
          id="regEmail" 
          class="form-control" 
          placeholder="nama@email.com" 
          value="<?= e($emailVal) ?>" 
          required
        >
      </div>

      <div class="form-group">
        <label for="regPassword"><i class="fas fa-lock text-pink"></i> Kata Sandi (Min. 6 Karakter):</label>
        <input 
          type="password" 
          name="password" 
          id="regPassword" 
          class="form-control" 
          placeholder="Buat kata sandi aman" 
          required
        >
      </div>

      <div class="form-group">
        <label for="regPasswordConfirm"><i class="fas fa-check-double text-pink"></i> Ulangi Kata Sandi:</label>
        <input 
          type="password" 
          name="password_confirm" 
          id="regPasswordConfirm" 
          class="form-control" 
          placeholder="Ulangi kata sandi" 
          required
        >
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
        <i class="fas fa-user-check"></i> Daftar Sekarang
      </button>
    </form>

    <div style="text-align: center; margin-top: 24px; font-size: 0.92rem; color: var(--text-secondary);">
      Sudah memiliki akun FoodFinder? 
      <a href="<?= BASE_URL ?>login.php" style="font-weight: 700; color: var(--pink-primary);">Masuk di sini</a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
