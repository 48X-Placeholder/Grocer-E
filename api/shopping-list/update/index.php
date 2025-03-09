<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../functions/load.php";

// Check if user is authenticated
if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// Get data from request
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['itemId'], $data['productName'], $data['brand'], $data['category'], $data['quantityNeeded'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Extract values and sanitize inputs
$itemId = intval($data['itemId']);
$productName = trim($data['productName']);
$brand = trim($data['brand']);
$category = trim($data['category']);
$quantityNeeded = intval($data['quantityNeeded']);

// Step 1: Retrieve the ProductId from SHOPPING_LIST
$sql_get_product = "SELECT ProductId FROM SHOPPING_LIST WHERE ListItemId = ?";
$stmt_get_product = $conn->prepare($sql_get_product);
$stmt_get_product->bind_param('i', $itemId);
$stmt_get_product->execute();
$result = $stmt_get_product->get_result();
$productData = $result->fetch_assoc();
$stmt_get_product->close();

if (!$productData) {
    echo json_encode(['success' => false, 'message' => 'Shopping list item not found']);
    exit;
}

$productId = $productData['ProductId'];

// Step 2: Update LOCAL_PRODUCTS (ProductName, Brand, Category) using the fetched ProductId
$sql_update_product = "UPDATE LOCAL_PRODUCTS SET ProductName = ?, Brand = ?, Category = ? WHERE ProductId = ?";
$stmt_update_product = $conn->prepare($sql_update_product);
$stmt_update_product->bind_param("sssi", $productName, $brand, $category, $productId);

if (!$stmt_update_product->execute()) {
    echo json_encode(['success' => false, 'message' => 'Failed to update product details']);
    exit;
}
$stmt_update_product->close();

// Step 3: Update SHOPPING_LIST (QuantityNeeded)
$sql_update_shop = "UPDATE SHOPPING_LIST SET QuantityNeeded = ? WHERE ListItemId = ?";
$stmt_update_shop = $conn->prepare($sql_update_shop);
$stmt_update_shop->bind_param("ii", $quantityNeeded, $itemId);

if ($stmt_update_shop->execute()) {
    echo json_encode(['success' => true, 'message' => 'Item updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update shopping list item']);
}

$stmt_update_shop->close();
$conn->close();
?>
