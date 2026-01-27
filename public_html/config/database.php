<?php
/**
 * Database Configuration
 * 
 * ENVIRONMENT DETECTION:
 * - Uses environment variables if set (recommended for production)
 * - Falls back to hardcoded values for local development
 * 
 * FOR HOSTINGER:
 * Option 1: Set environment variables in hPanel → Advanced → Cron Jobs → Environment
 * Option 2: Edit the fallback values below directly after deployment
 */

// ============================================
// DATABASE CREDENTIALS
// ============================================
// Environment variables take priority (for production security)
// Fallback values are for local development

$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_NAME = getenv('DB_NAME') ?: 'babu_toys';           // Hostinger: u123456789_babutoys
$DB_USER = getenv('DB_USER') ?: 'root';                // Hostinger: u123456789_admin  
$DB_PASS = getenv('DB_PASS') ?: '';                    // Hostinger: YourSecurePassword
// ============================================

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4", $DB_USER, $DB_PASS, $options);
} catch (Exception $e) {
    // Log error for debugging (optional)
    // error_log("Database connection failed: " . $e->getMessage());
    
    // Don't exit - let the application handle the missing connection
    $pdo = null;
}
