<?php
require_once dirname(__FILE__) . '../../../../config.php';
require_once dirname(__FILE__) . '../../../../functions/load.php';
header('Content-Type: application/json');

// Check if user is authenticated
if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

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
$userId = cached_userid_info();

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

    $productId = $stmt_insert_product->insert_id;
    $stmt_insert_product->close();
} else {
    $productId = $product['ProductId'];
}

// Insert into INVENTORY
if ($expirationDate === NULL) {
    $sql_insert_inventory = "INSERT INTO inventory (ProductId, UserId, Quantity, ExpirationDate) VALUES (?, ?, ?, NULL)";
    $stmt_insert_inventory = $conn->prepare($sql_insert_inventory);
    $stmt_insert_inventory->bind_param('iii', $productId, $userId, $quantity);
} else {
    $sql_insert_inventory = "INSERT INTO inventory (ProductId, UserId, Quantity, ExpirationDate) VALUES (?, ?, ?, ?)";
    $stmt_insert_inventory = $conn->prepare($sql_insert_inventory);
    $stmt_insert_inventory->bind_param('iiis', $productId, $userId, $quantity, $expirationDate);
}

if ($stmt_insert_inventory->execute()) {
    // Log the action to user_activity_logs
    $conn_user_db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, 'user_db');
    if (!$conn_user_db->connect_error) {
        $log_stmt = $conn_user_db->prepare("INSERT INTO user_activity_logs (UserId, Action) VALUES (?, ?)");
        $log_action = "Inventory Item Added Manually — Product Name: $productName, Brand: $brand, Category: $category, Quantity: $quantity";
        if ($expirationDate !== NULL) {
            $log_action .= ", Expiration Date: $expirationDate";
        }
        $log_stmt->bind_param('is', $userId, $log_action);
        $log_stmt->execute();
        $log_stmt->close();
        $conn_user_db->close();
    }

    echo json_encode(['success' => true, 'message' => 'Item added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add inventory item']);
}

$stmt_insert_inventory->close();
$conn->close();
?>
