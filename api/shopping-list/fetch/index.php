<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../../../config.php"; // Ensure database connection
require_once __DIR__ . "/../../../functions/load.php";

// Check if user is authenticated
if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}
$userId = cached_userid_info();

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Query to fetch only unpurchased items
$sql = "SELECT sl.ListItemId, lp.ProductName, lp.Brand, lp.Category, sl.QuantityNeeded 
        FROM SHOPPING_LIST sl
        JOIN LOCAL_PRODUCTS lp ON sl.ProductId = lp.ProductId
        WHERE sl.UserId = ? AND sl.Purchased = 0";  // Exclude purchased items

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row; // Add each row to the data array
}

echo json_encode($data); // Output data as JSON

$stmt->close();
$conn->close();
?>
