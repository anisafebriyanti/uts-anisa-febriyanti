<?php
/**
 * FoodFinder - Global Header Template
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$title = isset($pageTitle) ? $pageTitle . ' - FoodFinder' : 'FoodFinder - Pencarian & Rekomendasi Tempat Makan Modern';
$description = isset($pageDesc) ? $pageDesc : 'Temukan tempat makan terbaik, kafe estetik untuk WFC, kuliner tradisional, dan rekomendasi resto sesuai budget & preferensimu.';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?></title>
  <meta name="description" content="<?= e($description) ?>">

  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23F05A9D'><path d='M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z'/></svg>">

  <!-- Google Fonts: Poppins & Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Font Awesome 6 Icons CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <!-- Main Style -->
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

  <script>
    window.FOODFINDER_BASE_URL = "<?= BASE_URL ?>";
  </script>
</head>
<body>

  <!-- Navbar -->
  <?php include __DIR__ . '/navbar.php'; ?>

  <!-- Global Flash Alert Notification -->
  <?php if ($flash): ?>
    <div class="container" style="margin-top: 20px;">
      <div class="alert alert-<?= e($flash['type']) ?>" role="alert">
        <i class="fas fa-<?= ($flash['type'] === 'success') ? 'check-circle' : (($flash['type'] === 'danger') ? 'exclamation-triangle' : 'info-circle') ?>"></i>
        <div><?= e($flash['message']) ?></div>
      </div>
    </div>
  <?php endif; ?>
