<?php
/**
 * FoodFinder Admin - Manajemen Pengguna
 */

require_once __DIR__ . '/auth_check.php';

$search = trim($_GET['q'] ?? '');

$sql = "
    SELECT u.*, 
           (SELECT COUNT(*) FROM reviews WHERE user_id = u.id) as total_reviews,
           (SELECT COUNT(*) FROM favorites WHERE user_id = u.id) as total_favorites
    FROM users u
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY u.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = 'Manajemen Pengguna';
$pageDesc = 'Daftar pengguna terdaftar dan akun administrator aplikasi';
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
      <div class="admin-panel">
        <div class="admin-panel-header">
          <form action="<?= BASE_URL ?>admin/users.php" method="GET" style="display: flex; gap: 10px;">
            <input 
              type="text" 
              name="q" 
              value="<?= e($search) ?>" 
              placeholder="Cari nama atau email..." 
              class="form-control" 
              style="width: 260px; padding: 8px 14px; font-size: 0.9rem;"
            >
            <button type="submit" class="btn btn-outline btn-sm">Cari</button>
            <?php if (!empty($search)): ?>
              <a href="<?= BASE_URL ?>admin/users.php" class="btn btn-sm" style="color: var(--danger);">Reset</a>
            <?php endif; ?>
          </form>

          <div>
            <span style="font-size: 0.9rem; color: var(--admin-text-muted);">
              Total: <strong><?= count($users) ?></strong> Pengguna Terdaftar
            </span>
          </div>
        </div>

        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Pengguna</th>
                <th>Email</th>
                <th>Peran (Role)</th>
                <th>Total Ulasan</th>
                <th>Favorit</th>
                <th>Terdaftar Sejak</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
                <tr>
                  <td>#<?= (int)$u['id'] ?></td>
                  <td>
                    <div style="display: flex; align-items: center; gap: 12px;">
                      <div class="user-avatar-circle" style="width: 36px; height: 36px;">
                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                      </div>
                      <strong><?= e($u['name']) ?></strong>
                    </div>
                  </td>
                  <td><?= e($u['email']) ?></td>
                  <td>
                    <?php if ($u['role'] === 'admin'): ?>
                      <span class="badge" style="background: #FEE2E2; color: #DC2626; font-weight: 700;">
                        <i class="fas fa-shield"></i> Administrator
                      </span>
                    <?php else: ?>
                      <span class="badge badge-pink">
                        <i class="fas fa-user"></i> Pengguna Umum
                      </span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span style="font-weight: 600;"><?= (int)$u['total_reviews'] ?> ulasan</span>
                  </td>
                  <td>
                    <span style="color: var(--pink-primary); font-weight: 600;">
                      <i class="fas fa-heart"></i> <?= (int)$u['total_favorites'] ?>
                    </span>
                  </td>
                  <td style="color: var(--admin-text-muted); font-size: 0.85rem;">
                    <?= date('d M Y, H:i', strtotime($u['created_at'])) ?> WIB
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <script src="<?= BASE_URL ?>assets/js/script.js"></script>
</body>
</html>
