<?php
require_once dirname(__FILE__) . '../../../../config.php';
require_once dirname(__FILE__) . '../../../../functions/load.php';
header('Content-Type: application/json');

if (!is_user_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}
$user_id = cached_userid_info();

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
file_put_contents('debug_log.txt', print_r($data, true));

if (!$data) {
    echo json_encode(["success" => false, "message" => "No data received"]);
    exit;
}

$data = array_change_key_case($data, CASE_LOWER);
$requiredFields = ['itemid', 'productname', 'brand', 'category', 'quantity'];
$missingFields = [];
foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        $missingFields[] = $field;
    }
}
if (!empty($missingFields)) {
    echo json_encode(["success" => false, "message" => "Missing required fields", "missing" => $missingFields]);
    exit;
}

$itemId = intval($data['itemid']);
$productName = trim($data['productname']);
$brand = trim($data['brand']);
$category = trim($data['category']);
$quantity = intval($data['quantity']);
$expirationDate = isset($data['expirationdate']) && trim($data['expirationdate']) !== "" ? $data['expirationdate'] : NULL;
$upc = isset($data['upc']) && trim($data['upc']) !== "" ? trim($data['upc']) : NULL;

$sql_get_product = "SELECT ProductId FROM inventory WHERE InventoryItemId = ? AND UserId = ?";
$stmt_get_product = $conn->prepare($sql_get_product);
$stmt_get_product->bind_param('ii', $itemId, $user_id);
$stmt_get_product->execute();
$result = $stmt_get_product->get_result();
$productData = $result->fetch_assoc();
$stmt_get_product->close();

if (!$productData) {
    echo json_encode(["success" => false, "message" => "Inventory item not found or not owned by user"]);
    exit;
}

$productId = $productData['ProductId'];

$sql_old_values = "SELECT lp.ProductName, lp.Brand, lp.Category, lp.UPC, i.Quantity, i.ExpirationDate FROM local_products lp JOIN inventory i ON lp.ProductId = i.ProductId WHERE i.InventoryItemId = ? AND i.UserId = ?";
$stmt_old_values = $conn->prepare($sql_old_values);
$stmt_old_values->bind_param("ii", $itemId, $user_id);
$stmt_old_values->execute();
$result_old = $stmt_old_values->get_result();
$oldData = $result_old->fetch_assoc();
$stmt_old_values->close();

$sql_update_product = "UPDATE local_products SET ProductName = ?, Brand = ?, Category = ?, UPC = ? WHERE ProductId = ?";
$stmt_update_product = $conn->prepare($sql_update_product);
$stmt_update_product->bind_param("ssssi", $productName, $brand, $category, $upc, $productId);

if (!$stmt_update_product->execute()) {
    echo json_encode(["success" => false, "message" => "Failed to update product details"]);
    exit;
}
$stmt_update_product->close();

$sql_check_merge = "SELECT InventoryItemId, Quantity FROM inventory WHERE ProductId = ? AND ExpirationDate <=> ? AND InventoryItemId != ? AND UserId = ?";
$stmt_check_merge = $conn->prepare($sql_check_merge);
$stmt_check_merge->bind_param('isii', $productId, $expirationDate, $itemId, $user_id);
$stmt_check_merge->execute();
$result = $stmt_check_merge->get_result();
$existingRow = $result->fetch_assoc();
$stmt_check_merge->close();

if ($existingRow) {
    $existingItemId = $existingRow['InventoryItemId'];
    $newQuantity = $existingRow['Quantity'] + $quantity;

    $sql_update_merge = "UPDATE inventory SET Quantity = ? WHERE InventoryItemId = ?";
    $stmt_update_merge = $conn->prepare($sql_update_merge);
    $stmt_update_merge->bind_param('ii', $newQuantity, $existingItemId);
    $stmt_update_merge->execute();
    $stmt_update_merge->close();

    $sql_delete_old = "DELETE FROM inventory WHERE InventoryItemId = ?";
    $stmt_delete_old = $conn->prepare($sql_delete_old);
    $stmt_delete_old->bind_param('i', $itemId);
    $stmt_delete_old->execute();
    $stmt_delete_old->close();

    echo json_encode(["success" => true, "message" => "Item merged successfully"]);
} else {
    $sql_update_inventory = "UPDATE inventory SET Quantity = ?, ExpirationDate = ? WHERE InventoryItemId = ?";
    $stmt_update_inventory = $conn->prepare($sql_update_inventory);
    $stmt_update_inventory->bind_param("isi", $quantity, $expirationDate, $itemId);

    if ($stmt_update_inventory->execute()) {
        if ($oldData) {
            $logConn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_ACCOUNTS);
            if (!$logConn->connect_error) {
                $changes = [];
                $changeSummary = [];

                if ($oldData['ProductName'] !== $productName) {
                    $changes[] = "Product Name";
                    $changeSummary[] = "New Product Name: $productName";
                }
                if ($oldData['Brand'] !== $brand) {
                    $changes[] = "Brand";
                    $changeSummary[] = "New Brand: $brand";
                }
                if ($oldData['Category'] !== $category) {
                    $changes[] = "Category";
                    $changeSummary[] = "New Category: $category";
                }
                if (intval($oldData['Quantity']) !== $quantity) {
                    $changes[] = "Quantity";
                    $changeSummary[] = "New Quantity: $quantity";
                }
                if (($oldData['UPC'] ?? '') !== ($upc ?? '')) {
                    $changes[] = "UPC";
                    $changeSummary[] = "New UPC: " . ($upc ?? 'NULL');
                }
                if (($oldData['ExpirationDate'] ?? '') !== ($expirationDate ?? '')) {
                    $changes[] = "Expiration Date";
                    $changeSummary[] = "New Expiration Date: " . ($expirationDate ?? 'NULL');
                }                

                $sql_extra = "SELECT UPC FROM local_products WHERE ProductId = ?";
                $stmt_extra = $conn->prepare($sql_extra);
                $stmt_extra->bind_param("i", $productId);
                $stmt_extra->execute();
                $result_extra = $stmt_extra->get_result();
                $oldUPCData = $result_extra->fetch_assoc();
                $stmt_extra->close();

                $sql_exp = "SELECT ExpirationDate FROM inventory WHERE InventoryItemId = ?";
                $stmt_exp = $conn->prepare($sql_exp);
                $stmt_exp->bind_param("i", $itemId);
                $stmt_exp->execute();
                $result_exp = $stmt_exp->get_result();
                $oldExpData = $result_exp->fetch_assoc();
                $stmt_exp->close();

                $oldUPC = $oldUPCData['UPC'] ?? null;
                $oldExp = $oldExpData['ExpirationDate'] ?? null;

                if ($oldUPC !== $upc) {
                    $changes[] = "UPC";
                    $changeSummary[] = "New UPC: " . ($upc ?? 'NULL');
                }
                if (($oldExp ?? '') !== ($expirationDate ?? '')) {
                    $changes[] = "Expiration Date";
                    $changeSummary[] = "New Expiration Date: " . ($expirationDate ?? 'NULL');
                }

                if (!empty($changes)) {
                    $fieldsChanged = implode(", ", $changes);
                    $summaryStr = implode(", ", $changeSummary);
                    $oldName = $oldData['ProductName'];

                    $logStmt = $logConn->prepare("INSERT INTO user_activity_logs (UserId, Action, ActionTimestamp) VALUES (?, ?, NOW())");
                    $action = "Inventory Item Update — Updated $fieldsChanged for item '$oldName'. $summaryStr";
                    $logStmt->bind_param("is", $user_id, $action);
                    $logStmt->execute();
                    $logStmt->close();
                }
                $logConn->close();
            }
        }
        echo json_encode(["success" => true, "message" => "Item updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update inventory item"]);
    }
    $stmt_update_inventory->close();
}
$conn->close();
?>
