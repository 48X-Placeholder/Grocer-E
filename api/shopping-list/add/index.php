<?php
require_once dirname(__FILE__) . '../../../../config.php'; // Ensure database connection
require_once dirname(__FILE__) . '../../../../functions/load.php';
header('Content-Type: application/json');

// Check if user is authenticated
if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}
$userId = cached_userid_info();

// Primary DB connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Connect to user_db for logging
$logConn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_ACCOUNTS);
if ($logConn->connect_error) {
    error_log("Logging DB connection failed: " . $logConn->connect_error);
    $logConn = null; // fallback
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['productName'], $data['brand'], $data['category'], $data['quantityNeeded'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$productName = trim($data['productName']);
$brand = trim($data['brand']);
$category = trim($data['category']);
$quantityNeeded = intval($data['quantityNeeded']);

// Check if product exists
$sql_check = "SELECT ProductId FROM local_products WHERE ProductName = ? AND Brand = ? AND Category = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param('sss', $productName, $brand, $category);
$stmt_check->execute();
$result = $stmt_check->get_result();
$product = $result->fetch_assoc();
$stmt_check->close();

if (!$product) {
    $sql_insert_product = "INSERT INTO local_products (UserId, ProductName, Brand, Category) VALUES (?, ?, ?, ?)";
    $stmt_insert_product = $conn->prepare($sql_insert_product);
    $stmt_insert_product->bind_param('isss', $userId, $productName, $brand, $category);
    if ($stmt_insert_product->execute()) {
        $productId = $stmt_insert_product->insert_id;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add product']);
        exit;
    }
    $stmt_insert_product->close();
} else {
    $productId = $product['ProductId'];
}

// Check if the item already exists in the shopping list
$sql_check_shop = "SELECT ListItemId, QuantityNeeded FROM shopping_list WHERE ProductId = ? AND UserId = ? AND Purchased = 0";
$stmt_check_shop = $conn->prepare($sql_check_shop);
$stmt_check_shop->bind_param('ii', $productId, $userId);
$stmt_check_shop->execute();
$result_shop = $stmt_check_shop->get_result();
$existingShopItem = $result_shop->fetch_assoc();
$stmt_check_shop->close();

if ($existingShopItem) {
    // Item exists → update quantity
    $newQuantity = $existingShopItem['QuantityNeeded'] + $quantityNeeded;
    $sql_update_shop = "UPDATE shopping_list SET QuantityNeeded = ? WHERE ListItemId = ?";
    $stmt_update_shop = $conn->prepare($sql_update_shop);
    $stmt_update_shop->bind_param('ii', $newQuantity, $existingShopItem['ListItemId']);

    if ($stmt_update_shop->execute()) {
        echo json_encode(['success' => true, 'message' => 'Item already exists in list, quantity has been updated successfully']);
        if ($logConn) {
            $action = "Updated quantity for Shopping List item - $productName, New Quantity: $newQuantity";
            $logStmt = $logConn->prepare("INSERT INTO user_activity_logs (UserId, Action, ActionTimestamp) VALUES (?, ?, NOW())");
            $logStmt->bind_param('is', $userId, $action);
            $logStmt->execute();
            $logStmt->close();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
    }

    $stmt_update_shop->close();
} else {
    // Item is new → insert into shopping_list
    $sql_insert_shop = "INSERT INTO shopping_list (ProductId, UserId, QuantityNeeded) VALUES (?, ?, ?)";
    $stmt_insert_shop = $conn->prepare($sql_insert_shop);
    $stmt_insert_shop->bind_param('iii', $productId, $userId, $quantityNeeded);

    if ($stmt_insert_shop->execute()) {
        echo json_encode(['success' => true, 'message' => 'Item added successfully']);
        if ($logConn) {
            $action = "Shopping List Item Added Manually — $productName, Quantity: $quantityNeeded";
            $logStmt = $logConn->prepare("INSERT INTO user_activity_logs (UserId, Action, ActionTimestamp) VALUES (?, ?, NOW())");
            $logStmt->bind_param('is', $userId, $action);
            $logStmt->execute();
            $logStmt->close();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add shopping list item']);
    }

    $stmt_insert_shop->close();
}

$conn->close();
if ($logConn) $logConn->close();
?>
