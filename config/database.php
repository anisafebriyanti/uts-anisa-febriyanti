<?php
/**
 * FoodFinder - Database Configuration & PDO Connection
 * Sistem Rekomendasi & Pencarian Tempat Makan
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Database (Mendukung MySQL Cloud, MySQL Lokal XAMPP, dan Otomatis Fallback SQLite untuk Vercel)
$db_host = getenv('DB_HOST');
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$db_name = getenv('DB_NAME') ?: 'foodfinder_db';
$db_port = getenv('DB_PORT') ?: '3306';

$pdo = null;

// 1. Coba koneksi ke MySQL jika host diatur atau berjalan di server lokal (XAMPP/Apache)
if (!empty($db_host) || !getenv('VERCEL')) {
    $host_to_connect = !empty($db_host) ? $db_host : '127.0.0.1';
    try {
        $dsn = "mysql:host={$host_to_connect};port={$db_port};dbname={$db_name};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 2,
        ];
        if (getenv('DB_SSL') === 'true' || getenv('MYSQL_ATTR_SSL_CA')) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        }
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    } catch (PDOException $e) {
        $pdo = null;
    }
}

// 2. Fallback otomatis ke SQLite (sangat ideal untuk Vercel agar langsung berjalan tanpa perlu sewa MySQL cloud)
if (!$pdo) {
    $sqlitePath = dirname(__DIR__) . '/database/foodfinder.sqlite';
    if (file_exists($sqlitePath)) {
        try {
            $pdo = new PDO("sqlite:" . $sqlitePath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            // Daftarkan fungsi kompatibilitas MySQL untuk SQLite
            $pdo->sqliteCreateFunction('NOW', function() {
                return date('Y-m-d H:i:s');
            });
            $pdo->sqliteCreateFunction('RAND', function() {
                return mt_rand() / mt_getrandmax();
            });
        } catch (PDOException $e) {
            $pdo = null;
        }
    }
}

if (!$pdo) {
    // Tampilkan pesan error jika kedua koneksi gagal
    die("<div style='font-family: sans-serif; padding: 30px; background: #fff0f6; color: #d83f83; border: 1px solid #f05a9d; border-radius: 8px; margin: 50px auto; max-width: 600px; text-align: center;'>
        <h2>Koneksi Database Gagal</h2>
        <p>Pastikan MySQL server sudah berjalan atau database SQLite tersedia di <code>database/foodfinder.sqlite</code>.</p>
    </div>");
}

// Base URL otomatis berdasarkan URL browser saat ini
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $projectRoot = preg_replace('#/(admin|ajax|api).*$#', '', $scriptDir);
    $baseUrl = rtrim($protocol . $host . $projectRoot, '/') . '/';
    define('BASE_URL', $baseUrl);
}

// Global App Constants
define('APP_NAME', 'FoodFinder');
define('APP_TAGLINE', 'Temukan Tempat Makan Favoritmu');
