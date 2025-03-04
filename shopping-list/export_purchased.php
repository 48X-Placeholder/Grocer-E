<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . "/../config.php"; // Ensure database connection

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

// Get the request data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['itemIds']) || !is_array($data['itemIds'])) {
    echo json_encode(['success' => false, 'message' => 'No valid items selected.']);
    exit;
}

$itemIds = $data['itemIds'];
$successCount = 0;
$errors = [];

foreach ($itemIds as $itemId) {
    // Fetch item details from SHOPPING_LIST
    $sql_fetch = "SELECT sl.ProductId, sl.QuantityNeeded, lp.UPC 
                  FROM SHOPPING_LIST sl
                  JOIN LOCAL_PRODUCTS lp ON sl.ProductId = lp.ProductId
                  WHERE sl.ListItemId = ? AND sl.UserId = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    $stmt_fetch->bind_param('ii', $itemId, $userId);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();
    $item = $result_fetch->fetch_assoc();
    $stmt_fetch->close();

    if (!$item) {
        $errors[] = "Item ID $itemId not found.";
        continue;
    }

    $productId = $item['ProductId'];
    $quantity = $item['QuantityNeeded'];
    $upc = !empty($item['UPC']) ? $item['UPC'] : "UNKNOWN";

    // Mark item as purchased in SHOPPING_LIST
    $sql_mark_purchased = "UPDATE SHOPPING_LIST SET Purchased = 1 WHERE ListItemId = ?";
    $stmt_mark_purchased = $conn->prepare($sql_mark_purchased);
    $stmt_mark_purchased->bind_param('i', $itemId);
    if (!$stmt_mark_purchased->execute()) {
        $errors[] = "Failed to mark item ID $itemId as purchased.";
        continue;
    }
    $stmt_mark_purchased->close();

    // Check if the product exists in INVENTORY with no expiration date
    $sql_check_inventory = "SELECT InventoryItemId, Quantity FROM INVENTORY WHERE ProductId = ? AND UserId = ? AND ExpirationDate IS NULL";
    $stmt_check_inventory = $conn->prepare($sql_check_inventory);
    $stmt_check_inventory->bind_param('ii', $productId, $userId);
    $stmt_check_inventory->execute();
    $result_check = $stmt_check_inventory->get_result();
    $existingItem = $result_check->fetch_assoc();
    $stmt_check_inventory->close();

    if ($existingItem) {
        // If exists with no expiration date, update quantity
        $newQuantity = $existingItem['Quantity'] + $quantity;
        $sql_update_inventory = "UPDATE INVENTORY SET Quantity = ? WHERE InventoryItemId = ?";
        $stmt_update_inventory = $conn->prepare($sql_update_inventory);
        $stmt_update_inventory->bind_param('ii', $newQuantity, $existingItem['InventoryItemId']);

        if ($stmt_update_inventory->execute()) {
            $successCount++;
        } else {
            $errors[] = "Failed to update inventory for ProductId $productId.";
        }
        $stmt_update_inventory->close();
    } else {
        // If no existing unexpired item, create a new entry
        $sql_insert_inventory = "INSERT INTO INVENTORY (ProductId, UserId, Quantity, ExpirationDate) VALUES (?, ?, ?, NULL)";
        $stmt_insert_inventory = $conn->prepare($sql_insert_inventory);
        $stmt_insert_inventory->bind_param('iii', $productId, $userId, $quantity);

        if ($stmt_insert_inventory->execute()) {
            $successCount++;
        } else {
            $errors[] = "Failed to add ProductId $productId to inventory.";
        }
        $stmt_insert_inventory->close();
    }
}

// Return a JSON response
$response = ['success' => true, 'message' => "$successCount items exported successfully."];
if (!empty($errors)) {
    $response['errors'] = $errors;
}

echo json_encode($response);
$conn->close();
exit;
?>
