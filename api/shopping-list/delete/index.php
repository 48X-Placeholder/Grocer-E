<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../../../config.php"; // Ensure database connection
require_once __DIR__ . "/../../../functions/load.php";

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
