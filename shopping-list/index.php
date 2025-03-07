<?php
session_start();
header('Content-Type: text/html');
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../page-templates/navigation-menu.php";

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
$userId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping List</title>
    <link rel="stylesheet" href="../assets/styles/list.css">
</head>
<body>

    <nav class="Navigation-Menu">
        <?php site_navigation_menu(); ?>
    </nav>

    <section class="list-section">
        <div class="list-search-bar">
            <input type="text" id="list-search" placeholder="Search within the list..." oninput="handleSearch()" />
            <button class="search-btn" onclick="clearSearch()">Clear Search</button>
        </div>
		
		<div class="page-title">
			<h2>Shopping List</h2>
		</div>

        <div class="table-container">
            <table class="grocery-list-table">
            <thead>
                <tr>
                    <th>Select</th>
                    <th onclick="sortTable(0, 'string', this)" data-order="asc">Product Name</th>
                    <th onclick="sortTable(1, 'string', this)" data-order="asc">Brand</th>
                    <th onclick="sortTable(2, 'string', this)" data-order="asc">Category</th>
                    <th onclick="sortTable(3, 'number', this)" data-order="asc">Quantity</th>
                    <th>Edit</th>
                </tr>
            </thead>
                
                <tbody id="shoppingTableBody">
                    <!-- JavaScript will populate this -->
                </tbody>
            </table>
        </div>

        <!-- Add Item Form (Initially hidden) -->
        <div class="add-item-form" id="addItemForm" style="display: none;">
            <h3>Add New Item</h3>
            <input type="text" id="productName" placeholder="Product Name" required>
            <input type="text" id="brand" placeholder="Brand" required>
            <input type="text" id="category" placeholder="Category" required>
            <input type="number" id="quantityNeeded" placeholder="Quantity" required>
            <button class="submit-btn" onclick="addShopItem()">Submit</button>
            <button class="cancel-btn" onclick="cancelAddItem()">Cancel</button>
        </div>

        <!-- Action Buttons (Add/Delete) -->
        <div class="list-actions">
            <a href="/scan/index.php?source=shopping_list" class="add-btn">Scan Item</a>
            <button class="add-btn" onclick="toggleAddItemForm()">Add Item Manually</button>
            <button class="delete-btn" onclick="deleteSelectedItems()">Delete Selected Items</button>
            <button class="export-btn" onclick="exportSelectedItems()">Export Selected Items to Inventory</button>
        </div>

        <!-- Edit Item Form (Initially Hidden) -->
        <div id="editShopItemForm" style="display: none;">
            <h3>Edit Item</h3>
            <input type="hidden" id="editShopItemId"> <!-- Hidden field for item ID -->

            <label>Product Name:</label>
            <input type="text" id="editShopProductName">

            <label>Brand:</label>
            <input type="text" id="editShopBrand">

            <label>Category:</label>
            <input type="text" id="editShopCategory">

            <label>Quantity:</label>
            <input type="number" id="editShopQuantity">

            <button onclick="updateShopItem()">Update</button>
            <button onclick="cancelShopEdit()">Cancel</button>
        </div>
    </section>

    <script src="../assets/js/ShopList.js"></script>
    <script src="../assets/js/Search.js"></script>
</body>
</html>
