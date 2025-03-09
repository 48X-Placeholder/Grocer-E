<?php
session_start();
require __DIR__ . "/../config.php";
header('Content-Type: application/json');

// Check if user is authenticated
if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated', 'message' => 'User not authenticated']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$barcode = $data['barcode'] ?? '';
$source = $data['source'] ?? 'inventory';

if (!$barcode) {
    echo json_encode(['success' => false, 'error' => 'No barcode provided', 'message' => 'No barcode provided']);
    exit;
}

// Fetch product data from Open Food Facts API
$api_url = "https://world.openfoodfacts.org/api/v0/product/$barcode.json";
$response = @file_get_contents($api_url);
$product_data = $response ? json_decode($response, true) : null;

if (!$product_data || !isset($product_data['status']) || $product_data['status'] != 1) {
    echo json_encode(['success' => false, 'error' => 'Product not found. Add manually?', 'message' => 'Product not found. Add manually?']);
    exit;
}

$product_name = $product_data['product']['product_name'] ?? "Unknown Product";
$brand = $product_data['product']['brands'] ?? "Unknown Brand";
$category = $product_data['product']['categories'] ?? "Unknown Category";
$upc = $barcode;

$user_id = cached_userid_info();

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed', 'message' => 'Database connection failed']);
    exit;
}

// Check if product exists in local_products
$product_check = $conn->query("SELECT ProductId FROM local_products WHERE UPC='$upc' AND UserId='$user_id'");
$product_id = ($product_check->num_rows > 0) ? $product_check->fetch_assoc()['ProductId'] : null;

if (!$product_id) {
    // Insert new product with Brand and Category
    $stmt = $conn->prepare("INSERT INTO local_products (UserId, UPC, ProductName, Brand, Category, AddedAt) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issss", $user_id, $upc, $product_name, $brand, $category);
    $stmt->execute();
    $product_id = $stmt->insert_id;
    $stmt->close();
} else {
    // Update Brand & Category in case they are missing
    $stmt = $conn->prepare("UPDATE local_products SET Brand = ?, Category = ? WHERE ProductId = ?");
    $stmt->bind_param("ssi", $brand, $category, $product_id);
    $stmt->execute();
    $stmt->close();
}

// Insert into correct table
if ($source === "shopping_list") {
    $check = $conn->query("SELECT 1 FROM shopping_list WHERE ProductId='$product_id' AND UserId='$user_id'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO shopping_list (ProductId, UserId, QuantityNeeded, AddedAt) VALUES (?, ?, 1, NOW())");
        $stmt->bind_param("ii", $product_id, $user_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'Product added to shopping list', "product_name" => $product_name]);
    } else {
        echo json_encode(['success' => false, 'message' => "Product already exists in shopping list", "product_name" => $product_name]);
    }
} else {
    // Insert into inventory
    $check = $conn->query("SELECT 1 FROM inventory WHERE ProductId='$product_id' AND UserId='$user_id'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO inventory (ProductId, UserId, Quantity, AddedAt) VALUES (?, ?, 1, NOW())");
        $stmt->bind_param("ii", $product_id, $user_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'Product added to inventory', "product_name" => $product_name]);
    } else {
        echo json_encode(['success' => false, 'message' => "Product already exists in inventory", "product_name" => $product_name]);
    }
}

$conn->close();
?>