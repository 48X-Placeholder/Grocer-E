<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config.php";

// Get data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['productName'], $data['brand'], $data['category'], $data['quantityNeeded'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$productName = trim($data['productName']);
$brand = trim($data['brand']);
$category = trim($data['category']);
$quantityNeeded = intval($data['quantityNeeded']);
$userId = 1; // TEMP FIX: Replace with session authentication later

// Check if product already exists in LOCAL_PRODUCTS
$sql_check = "SELECT ProductId FROM LOCAL_PRODUCTS WHERE ProductName = ? AND Brand = ? AND Category = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param('sss', $productName, $brand, $category);
$stmt_check->execute();
$result = $stmt_check->get_result();
$product = $result->fetch_assoc();
$stmt_check->close();

if (!$product) {
    // Insert new product into LOCAL_PRODUCTS
    $sql_insert_product = "INSERT INTO LOCAL_PRODUCTS (UserId, ProductName, Brand, Category) VALUES (?, ?, ?, ?)";
    $stmt_insert_product = $conn->prepare($sql_insert_product);
    $stmt_insert_product->bind_param('isss', $userId, $productName, $brand, $category);
    
    if ($stmt_insert_product->execute()) {
        $productId = $stmt_insert_product->insert_id; // Get new ProductId
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add product']);
        exit;
    }
    $stmt_insert_product->close();
} else {
    $productId = $product['ProductId'];
}

// Insert into SHOPPING_LIST
$sql_insert_shop = "INSERT INTO SHOPPING_LIST (ProductId, UserId, QuantityNeeded) VALUES (?, ?, ?)";
$stmt_insert_shop = $conn->prepare($sql_insert_shop);
$stmt_insert_shop->bind_param('iii', $productId, $userId, $quantityNeeded);

if ($stmt_insert_shop->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add shopping list item']);
}

$stmt_insert_shop->close();
$conn->close();
?>