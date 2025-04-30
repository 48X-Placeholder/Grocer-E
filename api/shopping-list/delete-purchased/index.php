<?php
require_once dirname(__FILE__) . '../../../../config.php';
require_once dirname(__FILE__) . '../../../../functions/load.php';
header('Content-Type: application/json');

if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Database connection
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

$userId = cached_userid_info();

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['itemIds']) || empty($data['itemIds'])) {
    echo json_encode(['success' => false, 'message' => 'No items selected for deletion']);
    exit;
}

$itemIds = array_filter($data['itemIds'], fn($id) => ctype_digit(strval($id)));

$deletedItems = [];
$errors = [];

foreach ($itemIds as $itemId) {
    // Fetch Product details before deletion
    $stmt_fetch = $conn->prepare("SELECT sl.ProductId, lp.ProductName FROM shopping_list sl 
                                  JOIN local_products lp ON sl.ProductId = lp.ProductId 
                                  WHERE sl.ListItemId = ? AND sl.UserId = ? AND sl.Purchased = 1");
    $stmt_fetch->bind_param('ii', $itemId, $userId);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();
    $item = $result_fetch->fetch_assoc();
    $stmt_fetch->close();

    if (!$item) {
        $errors[] = "Item ID $itemId not found or not eligible for deletion.";
        continue;
    }

    $productName = $item['ProductName'] ?? 'Unknown Product';

    // Delete purchased item
    $stmt_delete = $conn->prepare("DELETE FROM shopping_list WHERE ListItemId = ? AND Purchased = 1 AND UserId = ?");
    $stmt_delete->bind_param("ii", $itemId, $userId);

    if ($stmt_delete->execute()) {
        $deletedItems[] = $productName;

        // Log deletion in user_activity_logs
        $stmt_log_action = $userDbConn->prepare("INSERT INTO user_activity_logs (UserId, Action, ActionTimestamp) VALUES (?, ?, NOW())");
        $actionDescription = "Deleted Item in Purchase History — $productName";
        $stmt_log_action->bind_param('is', $userId, $actionDescription);

        if (!$stmt_log_action->execute()) {
            $errors[] = "Logging failed for $productName: " . $stmt_log_action->error;
            error_log("SQL Error (Logging Delete): " . $stmt_log_action->error);
        }

        $stmt_log_action->close();
    } else {
        $errors[] = "Failed to delete $productName: " . $stmt_delete->error;
        error_log("SQL Error (Delete Item): " . $stmt_delete->error);
    }

    $stmt_delete->close();
}

$response = ['success' => true, 'message' => count($deletedItems) . " items removed from history."];
if (!empty($errors)) {
    $response['errors'] = $errors;
    error_log("Delete Errors: " . json_encode($errors)); // Log all errors for debugging
}

echo json_encode($response);
$conn->close();
$userDbConn->close();
exit;
