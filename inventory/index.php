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
        <!-- Search Bar -->
        <div class="list-search-bar">
            <input type="text" id="list-search" placeholder="Search within the list..." />
            <button class="search-btn" onclick="searchList()">Search</button>
        </div>

        <div class="page-title">
			<h2>Inventory</h2>
		</div>

        <!-- Grocery Table -->
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
                        <th>Actions</th> <!-- New column for Edit button -->
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

        <!-- Action Buttons (Add/Delete) -->
        <div class="list-actions">
            <button class="add-btn" onclick="toggleAddItemForm()">Add Item</button>
            <button class="delete-btn" onclick="deleteSelectedItems()">Delete Selected Items</button>
            <a href="/scan/index.php?source=inventory" class="add-btn">Scan Items</a>

        </div>

        <div id="editItemForm" style="display: none;">
            <h3>Edit Item</h3>
            <input type="hidden" id="editItemId"> <!-- Hidden field for item ID -->

            <label>Product Name:</label>
            <input type="text" id="editProductName">

            <label>Brand:</label>
            <input type="text" id="editBrand">

            <label>Category:</label>
            <input type="text" id="editCategory">

            <label>Quantity:</label>
            <input type="number" id="editQuantity">

            <label>Expiration Date:</label>
            <input type="date" id="editExpirationDate">

            <button onclick="updateItem()">Update</button>
            <button onclick="cancelEdit()">Cancel</button>
        </div>
    </section>
        <!-- 
        ** Original buttons, can probably go back to something more along 
        these lines when we have the API set up and working properly **

        <div class="list-actions">
            <button class="add-btn">Add Item</button>
            <button class="delete-btn">Delete Item</button>
        </div>
    </section>
-->

    <!-- pull necessary JS code from List.js file -->
    <script src="../assets/js/Inventory.js"></script>
</body>
</html>