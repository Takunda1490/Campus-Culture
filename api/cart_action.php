<?php
header('Content-Type: application/json');
require_once '../config/db.php';

// Ensure session is active (db.php starts it, but safe to check)
if (session_status() === PHP_SESSION_NONE) session_start();

// Reference to session cart array: [product_id => quantity]
$cart = &$_SESSION['cart'];
if (!isset($cart)) $cart = [];

$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents('php://input'), true) ?? $_POST;

try {
    switch ($method) {
        case 'GET':
            // ---------- FETCH CART WITH PRODUCT DETAILS ----------
            $items = [];
            if (!empty($cart)) {
                // Build placeholders for IN clause
                $placeholders = implode(',', array_fill(0, count($cart), '?'));
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
                $stmt->execute(array_keys($cart));
                $products = $stmt->fetchAll();

                // Merge product info with quantities
                foreach ($products as $p) {
                    $items[] = [
                        'id' => $p['id'],
                        'name' => $p['name'],
                        'price' => (float)$p['price'],
                        'image_url' => $p['image_url'],
                        'quantity' => $cart[$p['id']],
                        'subtotal' => $p['price'] * $cart[$p['id']]
                    ];
                }
            }
            $total = array_sum(array_column($items, 'subtotal'));
            echo json_encode(['status' => 'success', 'data' => $items, 'total' => $total, 'count' => array_sum($cart)]);
            break;

        case 'POST':
            $action = $input['action'] ?? 'add';
            $productId = (int)($input['product_id'] ?? 0);
            $qty = max(1, (int)($input['quantity'] ?? 1));

            // ---------- ADD TO CART ----------
            if ($action === 'add') {
                // Verify product exists and has stock
                $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
                $stmt->execute([$productId]);
                $product = $stmt->fetch();
                if (!$product) throw new Exception('Product not found');
                if ($product['stock'] < $qty) throw new Exception('Not enough stock');

                $cart[$productId] = ($cart[$productId] ?? 0) + $qty;
                echo json_encode(['status' => 'success', 'message' => 'Added to cart', 'count' => array_sum($cart)]);
            }
            // ---------- UPDATE QUANTITY ----------
            elseif ($action === 'update') {
                if ($qty <= 0) {
                    unset($cart[$productId]); // Remove if zero/negative
                } else {
                    $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
                    $stmt->execute([$productId]);
                    $product = $stmt->fetch();
                    if (!$product || $product['stock'] < $qty) throw new Exception('Not enough stock');
                    $cart[$productId] = $qty;
                }
                echo json_encode(['status' => 'success', 'message' => 'Cart updated']);
            }
            // ---------- REMOVE SINGLE ITEM ----------
            elseif ($action === 'remove') {
                unset($cart[$productId]);
                echo json_encode(['status' => 'success', 'message' => 'Removed from cart']);
            }
            // ---------- CLEAR ENTIRE CART ----------
            elseif ($action === 'clear') {
                $cart = [];
                echo json_encode(['status' => 'success', 'message' => 'Cart cleared']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
