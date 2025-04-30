<?php
require_once dirname(__FILE__) . '../../../../config.php';
require_once dirname(__FILE__) . '../../../../functions/load.php';
header('Content-Type: application/json');

// Check if user is authenticated
if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$itemIds = isset($data['itemIds']) ? $data['itemIds'] : (isset($data['itemId']) ? [$data['itemId']] : null);

if (empty($itemIds) || !is_array($itemIds)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$ids = array_map('intval', $itemIds); // Sanitize IDs
$idsList = implode(',', $ids);

// Fetch product details before deletion
$sql_fetch = "
    SELECT i.InventoryItemId, lp.ProductName, lp.Brand, lp.Category, i.Quantity, i.ExpirationDate
    FROM inventory i
    JOIN local_products lp ON i.ProductId = lp.ProductId
    WHERE i.InventoryItemId IN ($idsList)
";

$result = $conn->query($sql_fetch);
$itemsToLog = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $itemsToLog[] = $row;
    }
}

// Proceed with deletion
$sql_delete = "DELETE FROM inventory WHERE InventoryItemId IN ($idsList)";
$deleteSuccess = $conn->query($sql_delete);

if ($deleteSuccess) {
    // Log each deletion
    $conn_user_db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_ACCOUNTS);
    if (!$conn_user_db->connect_error) {
        $userId = cached_userid_info();
        $log_stmt = $conn_user_db->prepare("INSERT INTO user_activity_logs (UserId, Action) VALUES (?, ?)");

        foreach ($itemsToLog as $item) {
            $productName = $item['ProductName'];
            $brand = $item['Brand'];
            $category = $item['Category'];
            $quantity = $item['Quantity'];
            $expiration = $item['ExpirationDate'];

            $action = "Inventory Item Deleted — Product Name: $productName, Brand: $brand, Category: $category, Quantity: $quantity";
            if ($expiration !== null) {
                $action .= ", Expiration Date: $expiration";
            }

            $log_stmt->bind_param('is', $userId, $action);
            $log_stmt->execute();
        }

        $log_stmt->close();
        $conn_user_db->close();
    }

    echo json_encode(['success' => true, 'message' => 'Items deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete items']);
}

$conn->close();
?>
