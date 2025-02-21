<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config.php";

// Query to fetch all shopping list items for the user
$userId = 1; // Temporary fix, replace with session-based authentication later
$sql = "SELECT sl.ListItemId, lp.ProductName, lp.Brand, lp.Category, sl.QuantityNeeded 
        FROM SHOPPING_LIST sl
        JOIN LOCAL_PRODUCTS lp ON sl.ProductId = lp.ProductId
        WHERE sl.UserId = ?";

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