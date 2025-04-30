<?php
require_once dirname(__FILE__) . '/../../../config.php';
require_once dirname(__FILE__) . '/../../../functions/load.php';
header('Content-Type: application/json');

if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$userId = cached_userid_info();

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    error_log("Database connection failed: " . $conn->connect_error);
    exit;
}

// Connect to user_db (Core Site Accounts database)
$userDbConn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_ACCOUNTS);
if ($userDbConn->connect_error) {
    echo json_encode(["success" => false, "message" => "User database connection failed"]);
    error_log("User database connection failed: " . $userDbConn->connect_error);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['itemIds']) || !is_array($data['itemIds']) || empty($data['itemIds'])) {
    echo json_encode(['success' => false, 'message' => 'No valid items selected.']);
    exit;
}

$itemIds = array_filter($data['itemIds'], fn($id) => ctype_digit(strval($id)));
$successCount = 0;
$errors = [];

foreach ($itemIds as $itemId) {
    // Fetch Product details including ProductName
    $stmt_fetch = $conn->prepare("SELECT sl.ProductId, sl.QuantityNeeded, lp.UPC, lp.ProductName 
                                  FROM shopping_list sl 
                                  JOIN local_products lp ON sl.ProductId = lp.ProductId 
                                  WHERE sl.ListItemId = ? AND sl.UserId = ?");
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
    $productName = $item['ProductName'] ?? 'Unknown Product'; // Ensure ProductName is not NULL

    // Mark item as purchased
    $stmt_mark_purchased = $conn->prepare("UPDATE shopping_list SET Purchased = 1 WHERE ListItemId = ?");
    $stmt_mark_purchased->bind_param('i', $itemId);
    if (!$stmt_mark_purchased->execute()) {
        $errors[] = "Failed to mark item ID $itemId as purchased: " . $stmt_mark_purchased->error;
        error_log("SQL Error (Mark Purchased): " . $stmt_mark_purchased->error);
        continue;
    }
    $stmt_mark_purchased->close();

    // Always insert a new inventory record
    $stmt_insert_inventory = $conn->prepare("
    INSERT INTO inventory (ProductId, UserId, Quantity, ExpirationDate)
    VALUES (?, ?, ?, NULL)
    ");
    $stmt_insert_inventory->bind_param('iii', $productId, $userId, $quantity);

    if (!$stmt_insert_inventory->execute()) {
    $errors[] = "Failed to add $productName to inventory: " . $stmt_insert_inventory->error;
    error_log("SQL Error (Insert Inventory): " . $stmt_insert_inventory->error);
    continue;
    }
    $stmt_insert_inventory->close();


    // Log the export action with a separate connection to user_db
    $stmt_log_action = $userDbConn->prepare("INSERT INTO user_activity_logs (UserId, Action, ActionTimestamp) VALUES (?, ?, NOW())");
    $actionDescription = "Shopping List Item Purchased, Sent to Inventory — $productName, Quantity: $quantity";
    $stmt_log_action->bind_param('is', $userId, $actionDescription);

    if (!$stmt_log_action->execute()) {
        $errors[] = "Logging failed for $productName: " . $stmt_log_action->error;
        error_log("SQL Error (Logging to user_db): " . $stmt_log_action->error);
    }

    $stmt_log_action->close();

    $successCount++;
}

$response = ['success' => true, 'message' => "$successCount items exported successfully."];
if (!empty($errors)) {
    $response['errors'] = $errors;
    error_log("Export Errors: " . json_encode($errors)); // Log all errors at once for debugging
}

echo json_encode($response);
$conn->close();
$userDbConn->close();
exit;
