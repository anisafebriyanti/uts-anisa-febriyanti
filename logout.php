<?php
/**
 * FoodFinder - Logout Script
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Hapus semua session
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

// Mulai session baru hanya untuk menampung flash message
session_start();
set_flash('info', 'Anda telah berhasil keluar dari akun FoodFinder. Sampai jumpa lagi!');
header('Location: ' . BASE_URL . 'index.php');
exit;
