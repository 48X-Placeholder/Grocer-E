<?php
// fetch_inventory.php

header('Content-Type: application/json');
require_once __DIR__ . 'config.php';

// Query to fetch inventory data along with product details
$sql = "SELECT 
            i.InventoryItemId, 
            lp.ProductName, 
            lp.Brand, 
            lp.Category, 
            i.Quantity, 
            i.ExpirationDate 
        FROM INVENTORY i
        JOIN LOCAL_PRODUCTS lp ON i.ProductId = lp.ProductId";

$result = $conn->query($sql);

// Initialize an array to hold the data
$data = [];

// Fetch data if available
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row; // Add each row to the data array
    }
}

// Output the data as a JSON response
echo json_encode($data);

// Close the connection
$conn->close();
?>