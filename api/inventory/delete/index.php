<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../functions/load.php";

// Check if user is authenticated
if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

// Handle both single and multiple deletions
$itemIds = isset($data['itemIds']) ? $data['itemIds'] : (isset($data['itemId']) ? [$data['itemId']] : null);

if (empty($itemIds) || !is_array($itemIds)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Convert item IDs to integers to prevent SQL injection
$ids = implode(',', array_map('intval', $itemIds)); // Create a comma-separated list of IDs

// DELETE query targeting InventoryItemId
$sql = "DELETE FROM inventory WHERE InventoryItemId IN ($ids)";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'Items deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete items']);
}

$conn->close();
?>
