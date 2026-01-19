<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Check admin authentication
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require __DIR__ . '/../../config/database.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['order_id']) || empty($input['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing order_id or status']);
    exit;
}

$orderId = (int) $input['order_id'];
$status = trim($input['status']);

// Validate status
$validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'];
if (!in_array($status, $validStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$status, $orderId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Order status updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Order not found or no changes made']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
