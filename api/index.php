<?php
/**
 * Vercel Serverless Entrypoint / Router for FoodFinder
 */

$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestUri = '/' . ltrim($requestUri, '/');

$baseDir = dirname(__DIR__);

// Root / Home
if ($requestUri === '/' || $requestUri === '/index.php') {
    require $baseDir . '/index.php';
    exit;
}

// Target file in base directory
$targetFile = $baseDir . $requestUri;

// Direct file exists (.php or directory)
if (file_exists($targetFile)) {
    if (is_dir($targetFile)) {
        if (file_exists($targetFile . '/index.php')) {
            require $targetFile . '/index.php';
            exit;
        }
    } else {
        $ext = pathinfo($targetFile, PATHINFO_EXTENSION);
        if ($ext === 'php') {
            require $targetFile;
            exit;
        }
    }
}

// Check with .php extension appended
if (file_exists($targetFile . '.php')) {
    require $targetFile . '.php';
    exit;
}

// Fallback to index.php
require $baseDir . '/index.php';
