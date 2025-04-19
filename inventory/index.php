<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../page-templates/navigation-menu.php"; 

if (!is_user_logged_in()) {
	header("Location: ".SITE_URL.'login'); // Redirect to dashboard
	exit(); // Ensure no further code is executed after redirect
}

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grocery List</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<? echo SITE_URL.'assets/styles/list.css'?>">
</head>
<body>
    
    <!-- Site Navigation -->
	<?php site_navigation_menu(); ?>
    
    <section class="list-section">
        <div class="page-title">
            <h2>Inventory</h2>
        </div>
        
        <div class="toggle-section">
            <button id="togglePurchasedItems" onclick="<? echo SITE_URL.'shopping-list'?>">View Shopping List</button>
        </div>
        
        <div class="list-search-bar">
            <input type="text" id="list-search" placeholder="Search within the list..." oninput="handleSearch()" />
            <button class="search-btn" onclick="clearSearch()">Clear Search</button>
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

        <!-- Add Item Modal (Initially hidden) -->
        <div id="addItemModalForm" class="add-modal">
            <div class="add-modal-content">
                <h3>Add New Item</h3>

                <!-- Input Container -->
                <div class="input-container">
                    <input type="text" id="upcCode" placeholder="UPC Code" required>
                    <input type="text" id="productName" placeholder="Product Name" required>
                    <input type="text" id="brand" placeholder="Brand" required>
                    <select id="category" required>
                        <option value="">Select a Category</option>
                        <option value="Dairy">Dairy</option>
                        <option value="Meat">Meat</option>
                        <option value="Vegetables">Vegetables</option>
                        <option value="Fruits">Fruits</option>
                        <option value="Beverages">Beverages</option>
                        <option value="Bakery">Bakery</option>
                        <option value="Frozen Foods">Frozen Foods</option>
                        <option value="Snacks">Snacks</option>
                        <option value="Canned Goods">Canned Goods</option>
                        <option value="Grains">Grains</option>
                        <option value="Condiments">Condiments</option>
                        <option value="Deli">Deli</option>
                        <option value="Seafood">Seafood</option>
                        <option value="Spices & Herbs">Spices & Herbs</option>
                        <option value="Pasta & Rice">Pasta & Rice</option>
                        <option value="Household Items">Household Items</option>
                        <option value="Personal Care">Personal Care</option>
                    </select>
                    <input type="number" id="quantity" placeholder="Quantity" required>
                    <input type="date" id="expirationDate" placeholder="Expiration Date" required>
                </div>

                <!-- Buttons -->
                <div class="button-container">
                    <button class="modal-submit-btn" onclick="addItem()">Submit</button>
                    <button class="modal-cancel-btn" onclick="closeItemForm()">Cancel</button>
                </div>
            </div>
        </div>

        <div class="list-actions">
            <button class="add-btn" onclick="showAddItemModal()">Add Item</button>
            <!-- Modal Structure -->
            <div id="addItemModal" class="modal">
                <div class="modal-content">
                    <h3>How would you like to add the item?</h3>
                    <a href="<? echo SITE_URL.'/scan?source=inventory'?>" class="modal-btn">Scan Item</a>
                    <a href="#" onclick="showItemForm(); closeModal();" class="modal-btn">Add Manually</a>
                    <button onclick="closeModal()">Cancel</button>
                </div>
            </div>
            <button class="delete-btn" onclick="deleteSelectedItems()">Delete Selected Items</button>
        </div>
    </section>

    <!-- pull necessary JS code from List.js file -->
    <script src="<? echo SITE_URL.'assets/js/Inventory.js'?>"></script>
    <script src="<? echo SITE_URL.'assets/js/Search.js'?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
</body>
</html>
