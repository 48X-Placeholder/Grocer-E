<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config.php";

// Get data from POST request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['itemIds']) || empty($data['itemIds'])) {
    echo json_encode(['success' => false, 'message' => 'No items selected for deletion']);
    exit;
}

// Convert item IDs to a safe format for SQL query
$itemIds = implode(',', array_map('intval', $data['itemIds']));

$sql = "DELETE FROM SHOPPING_LIST WHERE ListItemId IN ($itemIds)";
if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database deletion failed']);
}

$conn->close();
?>