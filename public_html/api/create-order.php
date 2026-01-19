<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Basic input validation
$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$quantity = intval($_POST['quantity'] ?? 1);
$product_id = intval($_POST['product_id'] ?? 0);

if ($name === '' || $phone === '' || $address === '' || $product_id <= 0 || $quantity <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

require __DIR__ . '/../config/database.php';

try {
    // Start transaction
    $pdo->beginTransaction();

    // Get product price and stock
    $stmt = $pdo->prepare('SELECT price, stock, name FROM products WHERE id = :id FOR UPDATE');
    $stmt->execute([':id' => $product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    if ($product['stock'] < $quantity) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Not enough stock']);
        exit;
    }

    $total = bcmul((string)$product['price'], (string)$quantity, 2);

    // Insert order
    $order_key = bin2hex(random_bytes(8));
    $insert = $pdo->prepare('INSERT INTO orders (order_key, product_id, name, phone, address, quantity, total_amount, status) VALUES (:order_key, :product_id, :name, :phone, :address, :quantity, :total, :status)');
    $insert->execute([
        ':order_key' => $order_key,
        ':product_id' => $product_id,
        ':name' => $name,
        ':phone' => $phone,
        ':address' => $address,
        ':quantity' => $quantity,
        ':total' => $total,
        ':status' => 'pending'
    ]);

    $order_id = $pdo->lastInsertId();

    // Deduct stock
    $upd = $pdo->prepare('UPDATE products SET stock = stock - :q WHERE id = :id');
    $upd->execute([':q' => $quantity, ':id' => $product_id]);

    $pdo->commit();

    echo json_encode(['success' => true, 'order_id' => (int)$order_id, 'order_key' => $order_key]);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit;
}
