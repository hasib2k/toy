<?php
/**
 * Database Configuration for Hostinger
 * 
 * INSTRUCTIONS:
 * 1. Go to Hostinger hPanel → Databases → MySQL Databases
 * 2. Create a new database and note the credentials
 * 3. Replace the values below with your Hostinger database credentials
 */

// ============================================
// HOSTINGER DATABASE CREDENTIALS - UPDATE THESE
// ============================================
$DB_HOST = 'localhost';                    // Usually 'localhost' on Hostinger
$DB_NAME = 'u123456789_toystore';          // Your database name (e.g., u123456789_toystore)
$DB_USER = 'u123456789_admin';             // Your database username
$DB_PASS = 'YourSecurePassword123!';       // Your database password
// ============================================

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4", $DB_USER, $DB_PASS, $options);
} catch (Exception $e) {
    // Log error in production (don't expose details)
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}
