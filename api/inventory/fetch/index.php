<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../functions/load.php";

// Check if user is authenticated
if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}
$user_id = cached_userid_info();

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Query to fetch inventory data along with product details
$sql = "SELECT 
            i.InventoryItemId, 
            lp.ProductName, 
            lp.Brand, 
            lp.Category, 
            lp.UPC,
            i.Quantity, 
            i.ExpirationDate 
        FROM inventory i
        JOIN local_products lp ON i.ProductId = lp.ProductId
        WHERE i.UserId = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Initialize an array to hold the data
$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);

$stmt->close();
$conn->close();
?>
