<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config.php";

// Get data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['itemId'], $data['purchased'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$itemId = intval($data['itemId']);
$purchased = $data['purchased'] ? 1 : 0;

$sql = "UPDATE SHOPPING_LIST SET Purchased = ? WHERE ListItemId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $purchased, $itemId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}

$stmt->close();
$conn->close();
?>