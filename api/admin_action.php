<?php
header('Content-Type: application/json');
require_once '../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// ---------- ADMIN AUTH GUARD ----------
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Admin only']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    // ---------- GET: LIST ALL ORDERS WITH ITEMS ----------
    if ($method === 'GET') {
        $stmt = $pdo->query('
            SELECT o.*, u.full_name, u.email
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
        ');
        $orders = $stmt->fetchAll();

        // Nested query: fetch items for each order
        foreach ($orders as &$order) {
            $items = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
            $items->execute([$order['id']]);
            $order['items'] = $items->fetchAll();
        }

        echo json_encode(['status' => 'success', 'data' => $orders]);
        return;
    }

    // ---------- POST: ADD NEW PRODUCT ----------
    if ($method === 'POST') {
        // Text fields from form-data (multipart/form-data for image upload)
        $name        = trim($_POST['name'] ?? '');
        $category    = trim($_POST['category'] ?? '');
        $price       = (float)($_POST['price'] ?? 0);
        $stock       = (int)($_POST['stock'] ?? 10);
        $description = trim($_POST['description'] ?? '');

        if (!$name || !$category || $price <= 0) {
            throw new Exception('Name, category, and price required');
        }

        // Default placeholder image
        $imageUrl = 'assets/img/placeholder.jpg';

        // Handle optional image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowed)) {
                $filename = uniqid('prod_') . '.' . $ext;
                $dest = __DIR__ . '/../assets/img/' . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $imageUrl = 'assets/img/' . $filename;
                }
            }
        }

        // Insert product record
        $stmt = $pdo->prepare('INSERT INTO products (name, category, price, stock, image_url, description) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $category, $price, $stock, $imageUrl, $description]);

        echo json_encode(['status' => 'success', 'message' => 'Product added', 'product_id' => $pdo->lastInsertId()]);
        return;
    }

    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
