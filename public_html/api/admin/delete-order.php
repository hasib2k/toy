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

if (!$input || empty($input['order_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing order_id']);
    exit;
}

$orderId = (int) $input['order_id'];

try {
    $stmt = $pdo->prepare('DELETE FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Order deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
