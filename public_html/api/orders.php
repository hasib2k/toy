<?php
header('Content-Type: application/json; charset=utf-8');

// NOTE: This endpoint currently does not require authentication. Add admin auth checks in production.
require __DIR__ . '/../config/database.php';

try {
    $stmt = $pdo->query('SELECT o.id,o.order_key,o.name,o.phone,o.address,o.quantity,o.total_amount,o.status,o.created_at,p.name AS product_name FROM orders o JOIN products p ON o.product_id = p.id ORDER BY o.created_at DESC LIMIT 200');
    $orders = $stmt->fetchAll();
    echo json_encode(['success' => true, 'orders' => $orders]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
