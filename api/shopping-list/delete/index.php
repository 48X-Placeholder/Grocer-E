<?php
require_once dirname(__FILE__) . '../../../../config.php';
require_once dirname(__FILE__) . '../../../../functions/load.php';
header('Content-Type: application/json');

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

// Logging DB connection
$logConn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_ACCOUNTS);
if ($logConn->connect_error) {
    error_log("Logging DB connection failed: " . $logConn->connect_error);
    $logConn = null;
}

// Get item IDs
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['itemIds']) || empty($data['itemIds'])) {
    echo json_encode(['success' => false, 'message' => 'No items selected for deletion']);
    exit;
}
$itemIds = array_map('intval', $data['itemIds']);
$itemIdsStr = implode(',', $itemIds);

// Get product names before deletion
$productNames = [];
$sql_names = "
    SELECT lp.ProductName
    FROM shopping_list sl
    JOIN local_products lp ON sl.ProductId = lp.ProductId
    WHERE sl.ListItemId IN ($itemIdsStr) AND sl.UserId = ?
";
$stmt_names = $conn->prepare($sql_names);
$stmt_names->bind_param('i', $userId);
$stmt_names->execute();
$result_names = $stmt_names->get_result();
while ($row = $result_names->fetch_assoc()) {
    $productNames[] = $row['ProductName'];
}
$stmt_names->close();

// Delete items
$sql = "DELETE FROM shopping_list WHERE ListItemId IN ($itemIdsStr)";
$deletionSuccess = $conn->query($sql);

if ($deletionSuccess === TRUE) {
    echo json_encode(['success' => true]);

    // Insert logs
    if ($logConn) {
        $logStmt = $logConn->prepare("INSERT INTO user_activity_logs (UserId, Action, ActionTimestamp) VALUES (?, ?, NOW())");

        foreach ($productNames as $productName) {
            $action = "Shopping List Item Deleted — " . $productName;
            $logStmt->bind_param('is', $userId, $action);
            $logStmt->execute();
        }

        $logStmt->close();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database deletion failed']);
}

$conn->close();
if ($logConn) $logConn->close();
?>
