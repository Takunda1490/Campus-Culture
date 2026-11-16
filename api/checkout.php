<?php
header('Content-Type: application/json');
require_once '../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// ---------- AUTH GUARD ----------
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Login required']);
    exit;
}

// ---------- CART GUARD ----------
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
    exit;
}

// Read shipping address from JSON or form
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$address = trim($input['shipping_address'] ?? '');
if (!$address) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Shipping address required']);
    exit;
}

try {
    // ---------- FETCH PRODUCTS & VALIDATE STOCK ----------
    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($cart));
    $products = $stmt->fetchAll();

    $total = 0;
    $orderItems = [];
    foreach ($products as $p) {
        $qty = $cart[$p['id']];
        if ($p['stock'] < $qty) throw new Exception("Insufficient stock for {$p['name']}");
        $subtotal = $p['price'] * $qty;
        $total += $subtotal;
        $orderItems[] = ['product_id' => $p['id'], 'quantity' => $qty, 'price' => $p['price']];
    }

    // ---------- TRANSACTION: ORDERS + ORDER_ITEMS + STOCK DECREMENT ----------
    $pdo->beginTransaction();

    // Insert order header
    $stmt = $pdo->prepare('INSERT INTO orders (user_id, total_amount, shipping_address) VALUES (?, ?, ?)');
    $stmt->execute([$_SESSION['user_id'], $total, $address]);
    $orderId = $pdo->lastInsertId();

    // Insert order lines and decrement stock
    $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)');
    foreach ($orderItems as $item) {
        $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
        $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?')->execute([$item['quantity'], $item['product_id']]);
    }

    $pdo->commit();

    // Clear cart after successful order
    $_SESSION['cart'] = [];

    echo json_encode(['status' => 'success', 'message' => 'Order placed', 'order_id' => $orderId, 'total' => $total]);
} catch (Exception $e) {
    $pdo->rollBack(); // Undo everything on error
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
