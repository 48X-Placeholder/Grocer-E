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
    exit;
}

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['itemIds']) || empty($data['itemIds'])) {
    echo json_encode(['success' => false, 'message' => 'No items selected for deletion']);
    exit;
}

// Convert item IDs to a safe format
$itemIds = implode(',', array_map('intval', $data['itemIds']));

// Ensure only purchased items are deleted
$sql = "DELETE FROM shopping_list WHERE ListItemId IN ($itemIds) AND Purchased = 1";
if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'Items successfully removed from history']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database deletion failed']);
}

$conn->close();
?>
