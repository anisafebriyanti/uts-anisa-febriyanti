<?php
/**
 * Vercel Serverless Entrypoint / Router for FoodFinder
 */

$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestUri = '/' . ltrim($requestUri, '/');

$baseDir = dirname(__DIR__);

// 1. Root / Home
if ($requestUri === '/' || $requestUri === '/index.php') {
    require $baseDir . '/index.php';
    exit;
}

$targetFile = $baseDir . $requestUri;

// 2. Layani File Statis (CSS, JS, Gambar, Font) jika diroute ke sini
$mimeTypes = [
    'css'   => 'text/css; charset=UTF-8',
    'js'    => 'application/javascript; charset=UTF-8',
    'png'   => 'image/png',
    'jpg'   => 'image/jpeg',
    'jpeg'  => 'image/jpeg',
    'gif'   => 'image/gif',
    'svg'   => 'image/svg+xml',
    'webp'  => 'image/webp',
    'ico'   => 'image/x-icon',
    'woff'  => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf'   => 'font/ttf',
    'eot'   => 'application/vnd.ms-fontobject',
    'map'   => 'application/json',
];

if (file_exists($targetFile) && !is_dir($targetFile)) {
    $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    if (isset($mimeTypes[$ext])) {
        header("Content-Type: " . $mimeTypes[$ext]);
        header("Cache-Control: public, max-age=86400");
        readfile($targetFile);
        exit;
    }
    if ($ext === 'php') {
        require $targetFile;
        exit;
    }
}

// 3. Jika folder memiliki index.php
if (is_dir($targetFile) && file_exists($targetFile . '/index.php')) {
    require $targetFile . '/index.php';
    exit;
}

// 4. Jika URL tanpa ekstensi .php (contoh: /search atau /recommendation)
if (file_exists($targetFile . '.php')) {
    require $targetFile . '.php';
    exit;
}

// 5. Fallback ke index.php
require $baseDir . '/index.php';
