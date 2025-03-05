<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . "/../config.php"; 

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}


// Get data from request
$data = json_decode(file_get_contents('php://input'), true);



if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

// Convert all keys to lowercase
$data = array_change_key_case($data, CASE_LOWER);

// Check for missing fields
$requiredFields = ['upccode', 'quantity', 'productname', 'brand', 'category'];
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
$expirationDate = isset($data['expirationdate']) && trim($data['expirationdate']) !== "" ? $data['expirationdate'] : NULL;
$productName = trim($data['productname']);
$brand = trim($data['brand']);
$category = trim($data['category']);


// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}
$userId = $_SESSION['user_id'];

// Check if product exists in LOCAL_PRODUCTS
// Check if product exists in LOCAL_PRODUCTS
$sql_check = "SELECT ProductId FROM local_products WHERE UPC = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param('s', $upcCode);
$stmt_check->execute();
$result = $stmt_check->get_result();
$product = $result->fetch_assoc();
$stmt_check->close();

if (!$product) {
    // Product does not exist, insert into LOCAL_PRODUCTS
    $sql_insert_product = "INSERT INTO local_products (UserId, UPC, ProductName, Brand, Category) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert_product = $conn->prepare($sql_insert_product);
    $stmt_insert_product->bind_param('issss', $userId, $upcCode, $productName, $brand, $category);

    if (!$stmt_insert_product->execute()) {
        error_log("SQL Error (Insert Product): " . $stmt_insert_product->error);
        echo json_encode(['success' => false, 'message' => 'Failed to add product to local_products', 'error' => $stmt_insert_product->error]);
        exit;
    }

    $productId = $stmt_insert_product->insert_id; // Get the new ProductId
    $stmt_insert_product->close();
} else {
    // Product exists, use existing ProductId
    $productId = $product['ProductId'];
}


// Insert into INVENTORY
$sql_insert_inventory = "INSERT INTO INVENTORY (ProductId, UserId, Quantity, ExpirationDate) VALUES (?, ?, ?, ?)";
$stmt_insert_inventory = $conn->prepare($sql_insert_inventory);

// If Expiration Date is NULL, bind accordingly
if ($expirationDate === NULL) {
    $sql_insert_inventory = "INSERT INTO inventory (ProductId, UserId, Quantity, ExpirationDate) VALUES (?, ?, ?, NULL)";
} else {
    $sql_insert_inventory = "INSERT INTO inventory (ProductId, UserId, Quantity, ExpirationDate) VALUES (?, ?, ?, ?)";
}

$stmt_insert_inventory = $conn->prepare($sql_insert_inventory);

if ($expirationDate === NULL) {
    $stmt_insert_inventory->bind_param('iii', $productId, $userId, $quantity);
} else {
    $stmt_insert_inventory->bind_param('iiis', $productId, $userId, $quantity, $expirationDate);
}



if ($stmt_insert_inventory->execute()) {
    echo json_encode(['success' => true, 'message' => 'Item added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add inventory item']);
}

$stmt_insert_inventory->close();
$conn->close();
?>
