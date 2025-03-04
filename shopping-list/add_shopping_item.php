<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . "/../config.php";

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}
$userId = $_SESSION['user_id'];

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

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

// Check if the item already exists in SHOPPING_LIST
$sql_check_shop = "SELECT ListItemId, QuantityNeeded FROM SHOPPING_LIST WHERE ProductId = ? AND UserId = ? AND Purchased = 0";
$stmt_check_shop = $conn->prepare($sql_check_shop);
$stmt_check_shop->bind_param('ii', $productId, $userId);
$stmt_check_shop->execute();
$result_shop = $stmt_check_shop->get_result();
$existingShopItem = $result_shop->fetch_assoc();
$stmt_check_shop->close();

if ($existingShopItem) {
    // Item exists → update the quantity instead of adding a duplicate row
    $newQuantity = $existingShopItem['QuantityNeeded'] + $quantityNeeded;
    $sql_update_shop = "UPDATE SHOPPING_LIST SET QuantityNeeded = ? WHERE ListItemId = ?";
    $stmt_update_shop = $conn->prepare($sql_update_shop);
    $stmt_update_shop->bind_param('ii', $newQuantity, $existingShopItem['ListItemId']);

    if ($stmt_update_shop->execute()) {
        echo json_encode(['success' => true, 'message' => 'Item already exists in list, quantity has been updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
    }

    $stmt_update_shop->close();
} else {
    // New item → insert into SHOPPING_LIST
    $sql_insert_shop = "INSERT INTO SHOPPING_LIST (ProductId, UserId, QuantityNeeded) VALUES (?, ?, ?)";
    $stmt_insert_shop = $conn->prepare($sql_insert_shop);
    $stmt_insert_shop->bind_param('iii', $productId, $userId, $quantityNeeded);

    if ($stmt_insert_shop->execute()) {
        echo json_encode(['success' => true, 'message' => 'Item added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add shopping list item']);
    }

    $stmt_insert_shop->close();
}

$conn->close();
?>
