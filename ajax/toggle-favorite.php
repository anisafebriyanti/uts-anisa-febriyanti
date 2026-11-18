<?php
/**
 * FoodFinder - AJAX Endpoint: Toggle Favorite
 */

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak diizinkan.']);
    exit;
}

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['status' => 'unauthorized', 'message' => 'Silakan masuk terlebih dahulu untuk menyimpan favorit.']);
    exit;
}

$userId = (int) $_SESSION['user']['id'];
$restaurantId = isset($_POST['restaurant_id']) ? (int) $_POST['restaurant_id'] : 0;

if ($restaurantId <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID Tempat makan tidak valid.']);
    exit;
}

try {
    // Pastikan restoran ada
    $checkResto = $pdo->prepare("SELECT id FROM restaurants WHERE id = ?");
    $checkResto->execute([$restaurantId]);
    if (!$checkResto->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Tempat makan tidak ditemukan.']);
        exit;
    }

    // Cek apakah sudah ada di favorit
    $checkFav = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND restaurant_id = ?");
    $checkFav->execute([$userId, $restaurantId]);
    $fav = $checkFav->fetch();

    if ($fav) {
        // Hapus dari favorit
        $delete = $pdo->prepare("DELETE FROM favorites WHERE id = ?");
        $delete->execute([$fav['id']]);
        echo json_encode([
            'status' => 'success',
            'action' => 'removed',
            'message' => 'Dihapus dari favorit.'
        ]);
    } else {
        // Tambahkan ke favorit
        $insert = $pdo->prepare("INSERT INTO favorites (user_id, restaurant_id) VALUES (?, ?)");
        $insert->execute([$userId, $restaurantId]);
        echo json_encode([
            'status' => 'success',
            'action' => 'added',
            'message' => 'Ditambahkan ke favorit.'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan sistem database.']);
}
