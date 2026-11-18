<?php
/**
 * FoodFinder Admin - Authentication & Access Gate
 * Memastikan hanya user dengan role 'admin' yang dapat mengakses
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Verifikasi role admin
require_admin();

$adminUser = current_user();
