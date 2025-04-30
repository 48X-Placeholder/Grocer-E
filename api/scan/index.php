<?php
require_once dirname(__FILE__) . '../../../config.php';
require_once dirname(__FILE__) . '../../../functions/load.php';
header('Content-Type: application/json');

if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$barcode = $data['barcode'] ?? '';
$source = $data['source'] ?? 'inventory';

if (!$barcode) {
    echo json_encode(['success' => false, 'error' => 'No barcode provided']);
    exit;
}

// Fetch product info from API
$api_url = "https://world.openfoodfacts.org/api/v0/product/$barcode.json";
$response = @file_get_contents($api_url);
$product_data = $response ? json_decode($response, true) : null;

if (!$product_data || !isset($product_data['status']) || $product_data['status'] != 1) {
    echo json_encode(['success' => false, 'error' => 'Product not found. Add manually?']);
    exit;
}

$product_name = $product_data['product']['product_name'] ?? "Unknown Product";
$brand = $product_data['product']['brands'] ?? "Unknown Brand";
$rawCategory = $product_data['product']['categories'] ?? "Unknown Category";
$upc = $barcode;

function mapCategory($category) {
    $predefined = ["Dairy", "Meat", "Vegetables", "Fruits", "Beverages", "Bakery", "Frozen Foods", "Snacks", "Canned Goods", "Grains", "Condiments", "Deli", "Seafood", "Spices & Herbs", "Pasta & Rice", "Household Items", "Personal Care"];
    foreach ($predefined as $pre) {
        if (stripos($category, $pre) !== false) return $pre;
    }
    return "Other";
}

$category = mapCategory($rawCategory);
$user_id = cached_userid_info();

// Connect to grocery_db
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Ensure local product exists
$product_check = $conn->query("SELECT ProductId FROM local_products WHERE UPC='$upc' AND UserId='$user_id'");
$product_id = ($product_check->num_rows > 0) ? $product_check->fetch_assoc()['ProductId'] : null;

if (!$product_id) {
    $stmt = $conn->prepare("INSERT INTO local_products (UserId, UPC, ProductName, Brand, Category, AddedAt) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issss", $user_id, $upc, $product_name, $brand, $category);
    $stmt->execute();
    $product_id = $stmt->insert_id;
    $stmt->close();
} else {
    $stmt = $conn->prepare("UPDATE local_products SET Brand = ?, Category = ? WHERE ProductId = ?");
    $stmt->bind_param("ssi", $brand, $category, $product_id);
    $stmt->execute();
    $stmt->close();
}

$actionType = 'ADD';
$amountChanged = 1;
$inventoryItemId = null;

if ($source === "shopping_list") {
    $check = $conn->query("SELECT ListItemId, QuantityNeeded FROM shopping_list WHERE ProductId='$product_id' AND UserId='$user_id' AND Purchased=0");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO shopping_list (ProductId, UserId, QuantityNeeded, Purchased, AddedAt) VALUES (?, ?, 1, 0, NOW())");
        $stmt->bind_param("ii", $product_id, $user_id);
        $stmt->execute();
        $stmt->close();
        $message = 'Product added to shopping list';
    } else {
        $row = $check->fetch_assoc();
        $newQuantity = $row['QuantityNeeded'] + 1;
        $stmt = $conn->prepare("UPDATE shopping_list SET QuantityNeeded = ? WHERE ProductId = ? AND UserId = ? AND Purchased = 0");
        $stmt->bind_param("iii", $newQuantity, $product_id, $user_id);
        $stmt->execute();
        $stmt->close();
        $message = 'Product quantity updated in shopping list';
    }
} else {
    $stmt = $conn->prepare("INSERT INTO inventory (ProductId, UserId, Quantity, AddedAt) VALUES (?, ?, 1, NOW())");
    $stmt->bind_param("ii", $product_id, $user_id);
    $stmt->execute();
    $inventoryItemId = $stmt->insert_id;
    $stmt->close();
    $message = 'Product added to inventory';
}

// Insert into scan_logs (applies to both cases)
$logStmt = $conn->prepare("INSERT INTO scan_logs (UserId, UPC, ActionType, AmountChanged, InventoryItemId) VALUES (?, ?, ?, ?, ?)");
$logStmt->bind_param("issii", $user_id, $upc, $actionType, $amountChanged, $inventoryItemId);
$logStmt->execute();
$logStmt->close();

echo json_encode(['success' => true, 'message' => $message, "product_name" => $product_name]);

$conn->close();
?>
