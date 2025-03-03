<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . "/../config.php";

//database connection, allows the shopping list to be populated after scanning. 
//In process_barcode.php we explicitly created a new mysqli connection so we have to 
//also create a $conn object here so the list gets populated. 
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Query to fetch all shopping list items for the user
$user_id = $_SESSION['user_id']; // Temporary fix, replace with session-based authentication later update: I think i fixed it with this code
$sql = "SELECT sl.ListItemId, lp.ProductName, lp.Brand, lp.Category, sl.QuantityNeeded 
        FROM shopping_list sl
        JOIN LOCAL_PRODUCTS lp ON sl.ProductId = lp.ProductId
        WHERE sl.UserId = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
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