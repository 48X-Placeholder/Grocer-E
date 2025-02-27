<?php
require __DIR__ . "/../config.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$barcode = $data['barcode'];

if (!$barcode) {
    echo json_encode(["error" => "No barcode provided"]);
    exit;
}

// Fetch product data from Open Food Facts API
$api_url = "https://world.openfoodfacts.org/api/v0/product/$barcode.json";
$response = @file_get_contents($api_url);

if ($response === false) {
    error_log("Error: Failed to fetch data from Open Food Facts for barcode $barcode");
    echo json_encode(["error" => "Product not found in Open Food Facts"]);
    exit;
}

$product_data = json_decode($response, true);

// Check if JSON decoding failed
if ($product_data === null) {
    error_log("Error: Failed to decode JSON from Open Food Facts for barcode $barcode");
    echo json_encode(["error" => "Invalid response from Open Food Facts"]);
    exit;
}

// Check if product is found based on the status field
if (!isset($product_data['status']) || $product_data['status'] != 1) {
    // Fallback to backup API (Open Source Alternative: OpenEANDB)
    $backup_api = "https://api.eandb.org/?ean=$barcode&token=free";
    $backup_response = @file_get_contents($backup_api);

    if ($backup_response === false) {
        error_log("Error: Failed to fetch data from OpenEANDB for barcode $barcode");
        echo json_encode(["error" => "Product not found in both databases"]);
        exit;
    }

    $backup_data = json_decode($backup_response, true);

    if ($backup_data === null || empty($backup_data['ean_data']['title'])) {
        echo json_encode(["error" => "Product not found. Would you like to manually add it?"]);
        exit;
    }

    $product_name = $backup_data['ean_data']['title'] ?? "Unknown Product";
    $brand = $backup_data['ean_data']['brand'] ?? "Unknown Brand";
    $category = $backup_data['ean_data']['category'] ?? "Unknown Category";
} else {
    $product_name = $product_data['product']['product_name'] ?? "Unknown Product";
    $brand = $product_data['product']['brands'] ?? "Unknown Brand";
    $category = $product_data['product']['categories'] ?? "Unknown Category";
}

$upc = $barcode;

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'] ?? 1; // Placeholder, use actual session user ID

// Connect to the database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Check if product exists in `local_products`
$product_check = $conn->query("SELECT ProductId FROM local_products WHERE UPC='$upc' AND UserId='$user_id'");
if (!$product_check) {
    error_log("Database query error: " . $conn->error);
    echo json_encode(["error" => "Database query failed"]);
    exit;
}

if ($product_check->num_rows == 0) {
    // Insert new product into local_products
    $stmt = $conn->prepare("INSERT INTO local_products (UserId, UPC, ProductName, Brand, Category, AddedAt) VALUES (?, ?, ?, ?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("issss", $user_id, $upc, $product_name, $brand, $category);
        if (!$stmt->execute()) {
            error_log("Database insert error: " . $stmt->error);
            echo json_encode(["error" => "Failed to insert product"]);
            exit;
        }
        $product_id = $stmt->insert_id;
        $stmt->close();
    } else {
        error_log("Prepare statement failed: " . $conn->error);
        echo json_encode(["error" => "Database error"]);
        exit;
    }
} else {
    $row = $product_check->fetch_assoc();
    $product_id = $row['ProductId'];
}

// Check if product already exists in shopping_list to prevent duplicates
$shopping_list_check = $conn->query("SELECT ListItemId FROM shopping_list WHERE ProductId='$product_id' AND UserId='$user_id'");
if (!$shopping_list_check) {
    error_log("Database query error: " . $conn->error);
    echo json_encode(["error" => "Database query failed"]);
    exit;
}

if ($shopping_list_check->num_rows == 0) {
    // Insert into shopping_list only if it does not already exist
    $stmt = $conn->prepare("INSERT INTO shopping_list (ProductId, UserId, QuantityNeeded, AddedAt, Purchased) VALUES (?, ?, 1, NOW(), 0)");
    if ($stmt) {
        $stmt->bind_param("ii", $product_id, $user_id);
        if (!$stmt->execute()) {
            error_log("Database insert error: " . $stmt->error);
            echo json_encode(["error" => "Failed to add product to shopping list"]);
            exit;
        }
        echo json_encode(["message" => "Product added successfully", "product_name" => $product_name]);
        $stmt->close();
    } else {
        error_log("Prepare statement failed: " . $conn->error);
        echo json_encode(["error" => "Database error"]);
        exit;
    }
} else {
    echo json_encode(["message" => "Product already exists in shopping list", "product_name" => $product_name]);
}

$conn->close();
?>



