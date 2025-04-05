<?php
require_once dirname(__FILE__) . '../../../../config.php'; // Ensure database connection
require_once dirname(__FILE__) . '../../../../functions/load.php';
header('Content-Type: application/json');

if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$userId = cached_userid_info();
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    error_log("Database connection failed: " . $conn->connect_error);
    exit;
}

// Connect to user_db for logging
$userDbConn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_ACCOUNTS);
if ($userDbConn->connect_error) {
    echo json_encode(["error" => "User database connection failed"]);
    error_log("User database connection failed: " . $userDbConn->connect_error);
    exit;
}

// Get selected item IDs from request
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['itemIds']) || empty($data['itemIds'])) {
    echo json_encode(['success' => false, 'message' => 'No items selected for restoration']);
    exit;
}

$itemIds = array_filter($data['itemIds'], fn($id) => ctype_digit(strval($id)));

$restoredItems = [];
$errors = [];

// Restore each item and log the action
foreach ($itemIds as $itemId) {
    // Retrieve product details before restoring
    $stmt_fetch = $conn->prepare("SELECT sl.ProductId, lp.ProductName FROM shopping_list sl 
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

    $productName = $item['ProductName'] ?? 'Unknown Product';

    // Restore the purchased item
    $stmt_restore = $conn->prepare("UPDATE shopping_list SET Purchased = 0, AddedAt = NOW() WHERE ListItemId = ? AND UserId = ?");
    $stmt_restore->bind_param("ii", $itemId, $userId);

    if ($stmt_restore->execute()) {
        $restoredItems[] = $productName;

        // Log action in user_activity_logs
        $stmt_log_action = $userDbConn->prepare("INSERT INTO user_activity_logs (UserId, Action, ActionTimestamp) VALUES (?, ?, NOW())");
        $actionDescription = "Shopping List Item Restored from Previous Purchases — $productName";
        $stmt_log_action->bind_param('is', $userId, $actionDescription);
        
        if (!$stmt_log_action->execute()) {
            $errors[] = "Logging failed for $productName: " . $stmt_log_action->error;
            error_log("SQL Error (Logging Restore): " . $stmt_log_action->error);
        }

        $stmt_log_action->close();
    } else {
        $errors[] = "Failed to restore $productName: " . $stmt_restore->error;
        error_log("SQL Error (Restore Item): " . $stmt_restore->error);
    }

    $stmt_restore->close();
}

$response = ['success' => true, 'message' => count($restoredItems) . " items restored successfully."];
if (!empty($errors)) {
    $response['errors'] = $errors;
    error_log("Restore Errors: " . json_encode($errors)); // Log all errors for debugging
}

echo json_encode($response);
$conn->close();
$userDbConn->close();
exit;
