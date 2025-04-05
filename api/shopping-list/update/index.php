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
$userId = cached_userid_info();

// Connect to primary DB
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// Connect to logging DB
$logConn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_ACCOUNTS);
if ($logConn->connect_error) {
    error_log("Logging DB connection failed: " . $logConn->connect_error);
    $logConn = null;
}

// Get and validate incoming data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['itemId'], $data['productName'], $data['brand'], $data['category'], $data['quantityNeeded'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$itemId = intval($data['itemId']);
$newProductName = trim($data['productName']);
$newBrand = trim($data['brand']);
$newCategory = trim($data['category']);
$newQuantity = intval($data['quantityNeeded']);

// Fetch current item info
$sql = "
    SELECT lp.ProductName, lp.Brand, lp.Category, sl.QuantityNeeded, sl.ProductId
    FROM shopping_list sl
    JOIN local_products lp ON sl.ProductId = lp.ProductId
    WHERE sl.ListItemId = ? AND sl.UserId = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $itemId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$existing = $result->fetch_assoc();
$stmt->close();

if (!$existing) {
    echo json_encode(['success' => false, 'message' => 'Item not found']);
    exit;
}

$productId = $existing['ProductId'];

// Track changes for local_products
$fieldsToUpdate = [];
$valuesToBind = [];
$types = '';
$changeSummary = [];

if ($existing['ProductName'] !== $newProductName) {
    $fieldsToUpdate[] = "ProductName = ?";
    $valuesToBind[] = $newProductName;
    $types .= 's';
    $changeSummary[] = "New Product Name: $newProductName";
}
if ($existing['Brand'] !== $newBrand) {
    $fieldsToUpdate[] = "Brand = ?";
    $valuesToBind[] = $newBrand;
    $types .= 's';
    $changeSummary[] = "New Brand: $newBrand";
}
if ($existing['Category'] !== $newCategory) {
    $fieldsToUpdate[] = "Category = ?";
    $valuesToBind[] = $newCategory;
    $types .= 's';
    $changeSummary[] = "New Category: $newCategory";
}

// Update local_products if needed
if (!empty($fieldsToUpdate)) {
    $updateSql = "UPDATE local_products SET " . implode(', ', $fieldsToUpdate) . " WHERE ProductId = ?";
    $types .= 'i'; // for ProductId
    $valuesToBind[] = $productId;

    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param($types, ...$valuesToBind);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to update product details']);
        exit;
    }
    $stmt->close();
}

// Update quantity if needed
$quantityChanged = false;
if ($existing['QuantityNeeded'] != $newQuantity) {
    $stmt = $conn->prepare("UPDATE shopping_list SET QuantityNeeded = ? WHERE ListItemId = ?");
    $stmt->bind_param("ii", $newQuantity, $itemId);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
        exit;
    }
    $stmt->close();
    $quantityChanged = true;
    $changeSummary[] = "New Quantity: $newQuantity";
}

// Log changes
if ((!empty($fieldsToUpdate) || $quantityChanged) && $logConn) {
    $fieldsChanged = [];

    if (!empty($fieldsToUpdate)) {
        foreach ($fieldsToUpdate as $fieldClause) {
            $field = trim(explode('=', $fieldClause)[0]);
            if ($field === 'ProductName') $fieldsChanged[] = "Product Name";
            if ($field === 'Brand') $fieldsChanged[] = "Brand";
            if ($field === 'Category') $fieldsChanged[] = "Category";
        }
    }

    if ($quantityChanged) $fieldsChanged[] = "Quantity";

    $fieldStr = implode(", ", $fieldsChanged);
    $summaryStr = implode(", ", $changeSummary);
    $originalName = $existing['ProductName'];

    $action = "Shopping List Item Update — Updated $fieldStr for item '$originalName'. $summaryStr";

    $logStmt = $logConn->prepare("INSERT INTO user_activity_logs (UserId, Action, ActionTimestamp) VALUES (?, ?, NOW())");
    $logStmt->bind_param('is', $userId, $action);
    $logStmt->execute();
    $logStmt->close();
}

echo json_encode(['success' => true, 'message' => 'Item updated successfully']);

$conn->close();
if ($logConn) $logConn->close();
?>
