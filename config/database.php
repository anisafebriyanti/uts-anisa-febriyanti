<?php
/**
 * FoodFinder - Database Configuration & PDO Connection
 * Sistem Rekomendasi & Pencarian Tempat Makan
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Database
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'foodfinder_db';

try {
    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // Tampilkan pesan error ramah pengguna jika database belum diimpor
    die("<div style='font-family: sans-serif; padding: 30px; background: #fff0f6; color: #d83f83; border: 1px solid #f05a9d; border-radius: 8px; margin: 50px auto; max-width: 600px; text-align: center;'>
        <h2>Koneksi Database Gagal</h2>
        <p>Pastikan MySQL XAMPP sudah berjalan dan database <strong>foodfinder_db</strong> sudah dibuat/diimpor dari folder <code>database/foodfinder_db.sql</code>.</p>
        <p><small style='color: #666;'>Pesan Teknis: " . htmlspecialchars($e->getMessage()) . "</small></p>
    </div>");
}

// Base URL otomatis berdasarkan URL browser saat ini
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $projectRoot = preg_replace('#/(admin|ajax).*$#', '', $scriptDir);
    $baseUrl = rtrim($protocol . $host . $projectRoot, '/') . '/';
    define('BASE_URL', $baseUrl);
}

// Global App Constants
define('APP_NAME', 'FoodFinder');
define('APP_TAGLINE', 'Temukan Tempat Makan Favoritmu');
