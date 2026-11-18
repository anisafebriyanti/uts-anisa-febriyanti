<?php
/**
 * FoodFinder - Navigation Bar
 */

$currentPage = basename($_SERVER['PHP_SELF']);
$currentUser = current_user();
?>

<header class="site-header">
  <div class="container">
    <nav class="navbar">
      <!-- Logo Brand -->
      <a href="<?= BASE_URL ?>index.php" class="brand-logo">
        <div class="brand-icon">
          <i class="fas fa-utensils"></i>
        </div>
        <span>FoodFinder<span class="dot">.</span></span>
      </a>

      <!-- Menu Navigasi Utama -->
      <ul class="nav-menu" id="navMenu">
        <li>
          <a href="<?= BASE_URL ?>index.php" class="nav-link <?= ($currentPage === 'index.php') ? 'active' : '' ?>">
            <i class="fas fa-home"></i> Beranda
          </a>
        </li>
        <li>
          <a href="<?= BASE_URL ?>search.php" class="nav-link <?= ($currentPage === 'search.php') ? 'active' : '' ?>">
            <i class="fas fa-compass"></i> Jelajahi
          </a>
        </li>
        <li>
          <a href="<?= BASE_URL ?>recommendation.php" class="nav-link <?= ($currentPage === 'recommendation.php') ? 'active' : '' ?>">
            <i class="fas fa-wand-magic-sparkles"></i> Rekomendasi
          </a>
        </li>
        <li>
          <a href="<?= BASE_URL ?>favorites.php" class="nav-link <?= ($currentPage === 'favorites.php') ? 'active' : '' ?>">
            <i class="fas fa-heart"></i> Favorit
          </a>
        </li>
        <li>
          <a href="<?= BASE_URL ?>about.php" class="nav-link <?= ($currentPage === 'about.php') ? 'active' : '' ?>">
            <i class="fas fa-circle-info"></i> Tentang
          </a>
        </li>
      </ul>

      <!-- Aksi Navigasi / Akun -->
      <div class="nav-actions">
        <?php if ($currentUser): ?>
          <div class="user-dropdown">
            <div class="user-trigger" id="userDropdownTrigger">
              <div class="user-avatar-circle">
                <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
              </div>
              <span class="user-name-label"><?= e($currentUser['name']) ?></span>
              <i class="fas fa-chevron-down text-muted" style="font-size: 0.75rem;"></i>
            </div>
            
            <div class="dropdown-menu" id="userDropdownMenu">
              <div style="padding: 10px 14px; border-bottom: 1px solid var(--pink-border);">
                <div style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem;"><?= e($currentUser['name']) ?></div>
                <div style="font-size: 0.78rem; color: var(--text-secondary);"><?= e($currentUser['email']) ?></div>
                <?php if ($currentUser['role'] === 'admin'): ?>
                  <span class="badge badge-pink" style="margin-top: 6px;">Administrator</span>
                <?php endif; ?>
              </div>

              <?php if ($currentUser['role'] === 'admin'): ?>
                <a href="<?= BASE_URL ?>admin/index.php" class="dropdown-item">
                  <i class="fas fa-gauge-high text-primary"></i> Dashboard Admin
                </a>
              <?php endif; ?>

              <a href="<?= BASE_URL ?>favorites.php" class="dropdown-item">
                <i class="fas fa-heart text-danger"></i> Tempat Favorit Saya
              </a>

              <div class="dropdown-divider"></div>

              <a href="<?= BASE_URL ?>logout.php" class="dropdown-item" style="color: var(--danger);">
                <i class="fas fa-right-from-bracket"></i> Keluar (Logout)
              </a>
            </div>
          </div>
        <?php else: ?>
          <a href="<?= BASE_URL ?>login.php" class="btn btn-outline btn-sm">
            <i class="fas fa-right-to-bracket"></i> Masuk
          </a>
          <a href="<?= BASE_URL ?>register.php" class="btn btn-primary btn-sm">
            <i class="fas fa-user-plus"></i> Daftar
          </a>
        <?php endif; ?>

        <!-- Hamburger Button untuk Layar Mobile -->
        <button class="nav-toggle" id="navToggle" aria-label="Buka Menu Navigasi">
          <i class="fas fa-bars"></i>
        </button>
      </div>
    </nav>
  </div>
</header>
