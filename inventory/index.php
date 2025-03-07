<?php
require_once __DIR__ . "/../page-templates/navigation-menu.php"; ?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grocery List</title>
    <link rel="stylesheet" href="../assets/styles/list.css">
</head>
<body>
    
    <!-- Site Navigation -->
	<?php site_navigation_menu(); ?>
    
    <section class="list-section">
        <div class="list-search-bar">
            <input type="text" id="list-search" placeholder="Search within the list..." oninput="handleSearch()" />
            <button class="search-btn" onclick="clearSearch()">Clear Search</button>
        </div>

        <div class="page-title">
            <h2>Inventory</h2>
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
                        <th onclick="sortTable(4, 'date', this)" data-order="asc">Expiration Date</th>
                        <th class="upc-header hidden">UPC</th>
                        <th>Alerts</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="groceryTableBody">
                    <!-- JavaScript will populate this -->
                </tbody>
            </table>
        </div>

        <!-- Add Item Form (Initially hidden) -->
        <div class="add-item-form" id="addItemForm" style="display: none;">
            <h3>Add New Item</h3>
            <input type="text" id="upcCode" placeholder="UPC Code" required>
            <input type="text" id="productName" placeholder="Product Name" required>
            <input type="text" id="brand" placeholder="Brand" required>
            <input type="text" id="category" placeholder="Category" required>
            <input type="number" id="quantity" placeholder="Quantity" required>
            <input type="date" id="expirationDate" placeholder="Expiration Date" required>
            <button class="submit-btn" onclick="addItem()">Submit</button>
            <button class="cancel-btn" onclick="cancelAddItem()">Cancel</button>
        </div>

        <div class="list-actions">
            <a href="/scan/index.php?source=inventory" class="add-btn">Scan Item</a>
            <button class="add-btn" onclick="toggleAddItemForm()">Add Item Manually</button>
            <button class="delete-btn" onclick="deleteSelectedItems()">Delete Selected Items</button>
        </div>
    </section>

    <!-- pull necessary JS code from List.js file -->
    <script src="../assets/js/Inventory.js"></script>
    <script src="../assets/js/Search.js"></script>
</body>
</html>
