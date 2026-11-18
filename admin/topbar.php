<?php
/**
 * FoodFinder Admin - Reusable Topbar Component
 */
$flash = get_flash();
?>

<header class="admin-topbar">
  <div class="admin-page-title-group">
    <h2><?= e($pageTitle ?? 'Panel Administrator') ?></h2>
    <p><?= e($pageDesc ?? 'Sistem Manajemen Konten & Data FoodFinder') ?></p>
  </div>

  <div class="admin-topbar-actions">
    <a href="<?= BASE_URL ?>index.php" target="_blank" class="btn btn-outline btn-sm">
      <i class="fas fa-globe"></i> Website
    </a>
    <a href="<?= BASE_URL ?>admin/restaurant-add.php" class="btn btn-primary btn-sm">
      <i class="fas fa-plus"></i> Tambah Resto
    </a>
  </div>
</header>

<!-- Alert Notifikasi Flash Admin -->
<?php if ($flash): ?>
  <div style="padding: 20px 32px 0;">
    <div class="alert alert-<?= e($flash['type']) ?>">
      <i class="fas fa-<?= ($flash['type'] === 'success') ? 'check-circle' : (($flash['type'] === 'danger') ? 'triangle-exclamation' : 'info-circle') ?>"></i>
      <div><?= e($flash['message']) ?></div>
    </div>
  </div>
<?php endif; ?>
