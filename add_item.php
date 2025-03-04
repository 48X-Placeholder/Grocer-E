<?php
header('Content-Type: application/json');
require_once 'config.php'; 
// ^^ Check that file correctly connects to database, will need to check AWS db

// Get data from request
$data = json_decode(file_get_contents('php://input'), true);

// Debugging: Log received data
file_put_contents('debug_log.txt', print_r($data, true));

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

// Convert all keys to lowercase
$data = array_change_key_case($data, CASE_LOWER);

// Check for missing fields
$requiredFields = ['upccode', 'quantity', 'expirationdate', 'productname', 'brand', 'category'];
$missingFields = [];

foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields', 'missing' => $missingFields]);
    exit;
}

// Extract and sanitize variables
$upcCode = trim($data['upccode']);
$quantity = intval($data['quantity']);
$expirationDate = $data['expirationdate'];
$productName = trim($data['productname']);
$brand = trim($data['brand']);
$category = trim($data['category']);
$userId = 1; // *** TEMP FIX: Assign everything to testuser1 (UserId = 1)

// Check if product exists in LOCAL_PRODUCTS
$sql_check = "SELECT ProductId FROM LOCAL_PRODUCTS WHERE UPC = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param('s', $upcCode);
$stmt_check->execute();
$result = $stmt_check->get_result();
$product = $result->fetch_assoc();
$stmt_check->close();

if (!$product) {
    // Product does not exist, insert into LOCAL_PRODUCTS first
    $sql_insert_product = "INSERT INTO LOCAL_PRODUCTS (UserId, UPC, ProductName, Brand, Category) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert_product = $conn->prepare($sql_insert_product);
    $stmt_insert_product->bind_param('issss', $userId, $upcCode, $productName, $brand, $category);
    
    if ($stmt_insert_product->execute()) {
        $productId = $stmt_insert_product->insert_id; // Get the new ProductId
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add product to LOCAL_PRODUCTS']);
        exit;
    }
    $stmt_insert_product->close();
} else {
    // Product exists, use existing ProductId
    $productId = $product['ProductId'];
}

// Insert into INVENTORY
$sql_insert_inventory = "INSERT INTO INVENTORY (ProductId, UserId, Quantity, ExpirationDate) VALUES (?, ?, ?, ?)";
$stmt_insert_inventory = $conn->prepare($sql_insert_inventory);
$stmt_insert_inventory->bind_param('iiis', $productId, $userId, $quantity, $expirationDate);

if ($stmt_insert_inventory->execute()) {
    echo json_encode(['success' => true, 'message' => 'Item added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add inventory item']);
}

$stmt_insert_inventory->close();
$conn->close();
?>