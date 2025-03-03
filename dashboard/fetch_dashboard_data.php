<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../config.php";

// Fetch latest 5 shopping list items
$sql_shopping = "SELECT lp.ProductName, lp.Brand, lp.Category, sl.QuantityNeeded 
                 FROM SHOPPING_LIST sl
                 JOIN LOCAL_PRODUCTS lp ON sl.ProductId = lp.ProductId
                 ORDER BY sl.AddedAt DESC
                 LIMIT 5";
$result_shopping = $conn->query($sql_shopping);
$shopping_list = $result_shopping->fetch_all(MYSQLI_ASSOC);

// Fetch latest 5 inventory items
$sql_inventory = "SELECT lp.ProductName, lp.Brand, lp.Category, i.Quantity, i.ExpirationDate 
                  FROM INVENTORY i
                  JOIN LOCAL_PRODUCTS lp ON i.ProductId = lp.ProductId
                  ORDER BY i.AddedAt DESC
                  LIMIT 5";
$result_inventory = $conn->query($sql_inventory);
$inventory_list = $result_inventory->fetch_all(MYSQLI_ASSOC);

// Return data as JSON
echo json_encode(["shoppingList" => $shopping_list, "inventoryList" => $inventory_list]);

$conn->close();
?>