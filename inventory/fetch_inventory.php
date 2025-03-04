<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . "/../config.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not authenticated."]);
    exit;
}
$user_id = $_SESSION['user_id'];

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
