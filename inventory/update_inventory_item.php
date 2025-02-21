<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

require_once __DIR__ . 'config.php'; // Ensure correct database connection

// Get data from request
$data = json_decode(file_get_contents("php://input"), true);

// Debugging: Log received data
file_put_contents('debug_log.txt', print_r($data, true));

if (!$data) {
    echo json_encode(["success" => false, "message" => "No data received"]);
    exit;
}

// Convert all keys to lowercase (ensures consistency)
$data = array_change_key_case($data, CASE_LOWER);

// Validate required fields
$requiredFields = ['itemid', 'productname', 'brand', 'category', 'quantity', 'expirationdate'];
$missingFields = [];

foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    echo json_encode(["success" => false, "message" => "Missing required fields", "missing" => $missingFields]);
    exit;
}

// Extract and sanitize input data
$itemId = intval($data['itemid']);
$productName = trim($data['productname']);
$brand = trim($data['brand']);
$category = trim($data['category']);
$quantity = intval($data['quantity']);
$expirationDate = !empty($data['expirationdate']) ? $data['expirationdate'] : NULL;
if ($expirationDate === NULL) {
    $sql_update_inventory = "UPDATE INVENTORY SET Quantity = ?, ExpirationDate = NULL WHERE InventoryItemId = ?";
    $stmt_update_inventory = $conn->prepare($sql_update_inventory);
    $stmt_update_inventory->bind_param("ii", $quantity, $itemId);
} else {
    $sql_update_inventory = "UPDATE INVENTORY SET Quantity = ?, ExpirationDate = ? WHERE InventoryItemId = ?";
    $stmt_update_inventory = $conn->prepare($sql_update_inventory);
    $stmt_update_inventory->bind_param("isi", $quantity, $expirationDate, $itemId);
}

// Step 1: Get the ProductId from INVENTORY
$sql_get_product = "SELECT ProductId FROM INVENTORY WHERE InventoryItemId = ?";
$stmt_get_product = $conn->prepare($sql_get_product);
$stmt_get_product->bind_param('i', $itemId);
$stmt_get_product->execute();
$result = $stmt_get_product->get_result();
$productData = $result->fetch_assoc();

if (!$productData) {
    echo json_encode(["success" => false, "message" => "Inventory item not found"]);
    exit;
}

$productId = $productData['ProductId'];
$stmt_get_product->close();

// Step 2: Update LOCAL_PRODUCTS (ProductName, Brand, Category)
$sql_update_product = "UPDATE LOCAL_PRODUCTS SET ProductName = ?, Brand = ?, Category = ? WHERE ProductId = ?";
$stmt_update_product = $conn->prepare($sql_update_product);
$stmt_update_product->bind_param("sssi", $productName, $brand, $category, $productId);

if (!$stmt_update_product->execute()) {
    echo json_encode(["success" => false, "message" => "Failed to update product details"]);
    exit;
}

$stmt_update_product->close();

// Step 3: Update INVENTORY (Quantity, ExpirationDate)
$sql_update_inventory = "UPDATE INVENTORY SET Quantity = ?, ExpirationDate = ? WHERE InventoryItemId = ?";
$stmt_update_inventory = $conn->prepare($sql_update_inventory);
$stmt_update_inventory->bind_param("isi", $quantity, $expirationDate, $itemId);

if ($stmt_update_inventory->execute()) {
    echo json_encode(["success" => true, "message" => "Item updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update inventory item"]);
}

$stmt_update_inventory->close();
$conn->close();

?>