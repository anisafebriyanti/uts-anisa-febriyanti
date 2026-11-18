<?php
/**
 * FoodFinder Admin - Reusable Sidebar Component
 */

$currentAdminFile = basename($_SERVER['PHP_SELF']);
?>

<aside class="admin-sidebar" id="adminSidebar">
  <div class="admin-sidebar-header">
    <a href="<?= BASE_URL ?>admin/index.php" class="admin-brand-logo">
      <div class="brand-icon" style="width: 36px; height: 36px; font-size: 1.1rem;">
        <i class="fas fa-utensils"></i>
      </div>
      <div>
        <span>FoodFinder</span>
        <span class="admin-badge-role">Admin</span>
      </div>
    </a>
  </div>

  <nav class="admin-nav">
    <div class="admin-nav-heading">Utama</div>
    <a href="<?= BASE_URL ?>admin/index.php" class="admin-nav-item <?= ($currentAdminFile === 'index.php') ? 'active' : '' ?>">
      <i class="fas fa-gauge-high"></i> Dashboard
    </a>

    <div class="admin-nav-heading">Manajemen Restoran</div>
    <a href="<?= BASE_URL ?>admin/restaurants.php" class="admin-nav-item <?= ($currentAdminFile === 'restaurants.php') ? 'active' : '' ?>">
      <i class="fas fa-store"></i> Data Tempat Makan
    </a>
    <a href="<?= BASE_URL ?>admin/restaurant-add.php" class="admin-nav-item <?= ($currentAdminFile === 'restaurant-add.php') ? 'active' : '' ?>">
      <i class="fas fa-square-plus"></i> Tambah Tempat Baru
    </a>
    <a href="<?= BASE_URL ?>admin/categories.php" class="admin-nav-item <?= ($currentAdminFile === 'categories.php') ? 'active' : '' ?>">
      <i class="fas fa-layer-group"></i> Kategori Kuliner
    </a>

    <div class="admin-nav-heading">Interaksi & Komunitas</div>
    <a href="<?= BASE_URL ?>admin/reviews.php" class="admin-nav-item <?= ($currentAdminFile === 'reviews.php') ? 'active' : '' ?>">
      <i class="fas fa-comments"></i> Moderasi Ulasan
    </a>
    <a href="<?= BASE_URL ?>admin/users.php" class="admin-nav-item <?= ($currentAdminFile === 'users.php') ? 'active' : '' ?>">
      <i class="fas fa-users"></i> Data Pengguna
    </a>

    <div class="admin-nav-heading">Situs Publik</div>
    <a href="<?= BASE_URL ?>index.php" target="_blank" class="admin-nav-item">
      <i class="fas fa-arrow-up-right-from-square"></i> Lihat Website Publik
    </a>
  </nav>

  <div class="admin-sidebar-footer">
    <div class="admin-user-pill">
      <div class="user-avatar-circle" style="width: 38px; height: 38px;">
        <i class="fas fa-user-shield"></i>
      </div>
      <div style="flex: 1; min-width: 0;">
        <div style="font-size: 0.88rem; font-weight: 700; color: var(--admin-text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
          <?= e($adminUser['name']) ?>
        </div>
        <div style="font-size: 0.75rem; color: var(--admin-text-muted);">Administrator</div>
      </div>
      <a href="<?= BASE_URL ?>logout.php" title="Keluar" style="color: var(--danger); padding: 6px;">
        <i class="fas fa-right-from-bracket"></i>
      </a>
    </div>
  </div>
</aside>
