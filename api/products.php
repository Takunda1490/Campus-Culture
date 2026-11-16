<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$category = $_GET['category'] ?? '';
$search   = $_GET['search'] ?? '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($category) && $category !== 'All') {
    $sql .= " AND category = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

echo json_encode(['status' => 'success', 'data' => $products]);
