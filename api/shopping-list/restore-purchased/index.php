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
    exit;
}

// Get selected item IDs from request
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['itemIds']) || empty($data['itemIds'])) {
    echo json_encode(['success' => false, 'message' => 'No items selected for restoration']);
    exit;
}

// Convert item IDs for safe SQL query
$itemIds = implode(',', array_map('intval', $data['itemIds']));

$sql = "UPDATE shopping_list SET Purchased = 0, AddedAt = NOW() WHERE ListItemId IN ($itemIds) AND UserId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to restore items']);
}

$stmt->close();
$conn->close();
?>
