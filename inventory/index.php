<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../page-templates/navigation-menu.php";

if (!is_user_logged_in()) {
	header("Location: ".SITE_URL.'login'); // Redirect to login page
	exit();
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    
    <!-- Include Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo SITE_URL.'assets/styles/list.css'?>">
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
            
            <!-- Standardized Category Dropdown -->
            <select id="category" required>
            <option value="">Select a Category</option>
                <option value="Fresh Produce">Fresh Produce</option>
                <option value="Dairy & Eggs">Dairy & Eggs</option>
                <option value="Meat & Seafood">Meat & Seafood</option>
                <option value="Deli & Prepared Foods">Deli & Prepared Foods</option>
                <option value="Bakery">Bakery</option>
                <option value="Frozen Foods">Frozen Foods</option>
                <option value="Pantry Staples (Dry Goods)">Pantry Staples (Dry Goods)</option>
                <option value="Snacks & Sweets">Snacks & Sweets</option>
                <option value="Beverages">Beverages</option>
                <option value="Cereal & Breakfast Foods">Cereal & Breakfast Foods</option>
                <option value="International Foods">International Foods</option>
                <option value="Organic & Health Foods">Organic & Health Foods</option>
                <option value="Baby & Toddler Food">Baby & Toddler Food</option>
                <option value="Pet Food">Pet Food</option>
            </select>
            
            <input type="number" id="quantity" placeholder="Quantity" required>
            <input type="date" id="expirationDate" placeholder="Expiration Date" required>
            <button class="submit-btn" onclick="addItem()">Submit</button>
            <button class="cancel-btn" onclick="cancelAddItem()">Cancel</button>
        </div>

        <div class="list-actions">
            <a href="<?php echo SITE_URL.'scan?source=inventory'?>" class="add-btn">Scan Item</a>
            <button class="add-btn" onclick="toggleAddItemForm()">Add Item Manually</button>
            <button class="delete-btn" onclick="deleteSelectedItems()">Delete Selected Items</button>
        </div>
    </section>

    <!-- Include jQuery and Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="<?php echo SITE_URL.'assets/js/Inventory.js'?>"></script>
    <script src="<?php echo SITE_URL.'assets/js/Search.js'?>"></script>
    <script>
    // Initialize Select2 for Category Dropdown
    $(document).ready(function() {
        $('#category').select2({
            placeholder: "Select a Category",
            width: 'resolve'
        });
    });
    </script>
</body>
</html>

