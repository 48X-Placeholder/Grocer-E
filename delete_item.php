<?php
// Delete item from inventory
header('Content-Type: application/json');
require_once 'config.php';

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);
$itemIds = $data['itemIds']; // Array of item IDs to delete

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