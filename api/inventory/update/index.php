<?php
require_once dirname(__FILE__) . '../../../../config.php'; // Ensure database connection
require_once dirname(__FILE__) . '../../../../functions/load.php';
header('Content-Type: application/json');

// Check if user is authenticated
if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}
$user_id = cached_userid_info();

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// Get data from request
$data = json_decode(file_get_contents("php://input"), true);

// Debugging: Log received data
file_put_contents('debug_log.txt', print_r($data, true));

if (!$data) {
    echo json_encode(["success" => false, "message" => "No data received"]);
    exit;
}

// Convert all keys to lowercase
$data = array_change_key_case($data, CASE_LOWER);

// Validate required fields
$requiredFields = ['itemid', 'productname', 'brand', 'category', 'quantity'];
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
$expirationDate = isset($data['expirationdate']) && trim($data['expirationdate']) !== "" ? $data['expirationdate'] : NULL;

// Handle UPC correctly: Set NULL if empty
$upc = isset($data['upc']) && trim($data['upc']) !== "" ? trim($data['upc']) : NULL;

// Get the ProductId and verify ownership in INVENTORY
$sql_get_product = "SELECT ProductId FROM inventory WHERE InventoryItemId = ? AND UserId = ?";
$stmt_get_product = $conn->prepare($sql_get_product);
$stmt_get_product->bind_param('ii', $itemId, $user_id);
$stmt_get_product->execute();
$result = $stmt_get_product->get_result();
$productData = $result->fetch_assoc();
$stmt_get_product->close();

if (!$productData) {
    echo json_encode(["success" => false, "message" => "Inventory item not found or not owned by user"]);
    exit;
}

$productId = $productData['ProductId'];

// Update LOCAL_PRODUCTS (ProductName, Brand, Category, UPC)
$sql_update_product = "UPDATE local_products SET ProductName = ?, Brand = ?, Category = ?, UPC = ? WHERE ProductId = ?";
$stmt_update_product = $conn->prepare($sql_update_product);
$stmt_update_product->bind_param("ssssi", $productName, $brand, $category, $upc, $productId);

if (!$stmt_update_product->execute()) {
    echo json_encode(["success" => false, "message" => "Failed to update product details"]);
    exit;
}
$stmt_update_product->close();

// Check if another row exists with the same product and expiration date (merging logic)
$sql_check_merge = "SELECT InventoryItemId, Quantity FROM inventory 
                    WHERE ProductId = ? AND ExpirationDate <=> ? AND InventoryItemId != ? AND UserId = ?";
$stmt_check_merge = $conn->prepare($sql_check_merge);
$stmt_check_merge->bind_param('isii', $productId, $expirationDate, $itemId, $user_id);
$stmt_check_merge->execute();
$result = $stmt_check_merge->get_result();
$existingRow = $result->fetch_assoc();
$stmt_check_merge->close();

if ($existingRow) {
    // Merge quantities and delete the duplicate row
    $existingItemId = $existingRow['InventoryItemId'];
    $newQuantity = $existingRow['Quantity'] + $quantity;

    $sql_update_merge = "UPDATE inventory SET Quantity = ? WHERE InventoryItemId = ?";
    $stmt_update_merge = $conn->prepare($sql_update_merge);
    $stmt_update_merge->bind_param('ii', $newQuantity, $existingItemId);
    $stmt_update_merge->execute();
    $stmt_update_merge->close();

    // Remove the duplicate row
    $sql_delete_old = "DELETE FROM inventory WHERE InventoryItemId = ?";
    $stmt_delete_old = $conn->prepare($sql_delete_old);
    $stmt_delete_old->bind_param('i', $itemId);
    $stmt_delete_old->execute();
    $stmt_delete_old->close();

    echo json_encode(["success" => true, "message" => "Item merged successfully"]);
} else {
    // No matching expiration date, update the current row
    $sql_update_inventory = "UPDATE inventory SET Quantity = ?, ExpirationDate = ? WHERE InventoryItemId = ?";
    $stmt_update_inventory = $conn->prepare($sql_update_inventory);
    $stmt_update_inventory->bind_param("isi", $quantity, $expirationDate, $itemId);

    if ($stmt_update_inventory->execute()) {
        echo json_encode(["success" => true, "message" => "Item updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update inventory item"]);
    }
    $stmt_update_inventory->close();
}

$conn->close();
?>
