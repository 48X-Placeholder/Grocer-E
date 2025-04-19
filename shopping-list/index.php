<?php
header('Content-Type: text/html');
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../page-templates/navigation-menu.php";

// Check if user is authenticated
if (!is_user_logged_in()) {
    header("Location: ".SITE_URL.'login'); // Redirect to dashboard
    exit;
}
$userId = cached_userid_info();
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping List</title>
    <link rel="stylesheet" href="<? echo SITE_URL.'assets/styles/list.css'?>">
	<link rel="icon" type="image/png" href="<? echo SITE_URL.'assets/images/grocer-e_favicon.png'?>">
</head>
<body>

    <?php site_navigation_menu(); ?>

    <section class="list-section">		
		<div class="page-title">
			<h2>Shopping List</h2>
		</div>

        <!-- Toggle Button for Shopping List / Previously Purchased Items -->
        <div class="toggle-section">
            <button id="togglePurchasedItems" onclick="togglePurchasedView()">View Purchased Items</button>
        </div>

        <div class="list-search-bar">
            <input type="text" id="list-search" placeholder="Search within the list..." oninput="handleSearch()" />
            <button class="search-btn" onclick="clearSearch()">Clear Search</button>
        </div>

        <div class="table-container" id="shoppingListContainer">
            <table class="grocery-list-table">
                <thead>
                    <tr>
                        <th>Select</th>
                        <th onclick="sortTable(0, 'string', this)" data-order="asc">Product Name</th>
                        <th onclick="sortTable(1, 'string', this)" data-order="asc">Brand</th>
                        <th onclick="sortTable(2, 'string', this)" data-order="asc">Category</th>
                        <th onclick="sortTable(3, 'number', this)" data-order="asc">Quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="shoppingTableBody">
                    <!-- JavaScript will populate this -->
                </tbody>
            </table>
        </div>

        <!-- Action Buttons (Add/Delete) -->
        <div class="list-actions">
            <button class="add-btn" onclick="showAddItemModal()">Add Item</button>

            <!-- Modal Structure -->
            <div id="addItemModal" class="modal">
                <div class="modal-content">
                    <h3>How would you like to add the item?</h3>
                    <a href="<? echo SITE_URL.'/scan?source=shopping_list'?>" class="modal-btn">Scan Item</a>
                    <a href="#" onclick="showItemForm(); closeModal();" class="modal-btn">Add Manually</a>
                    <button onclick="closeModal()">Cancel</button>
                </div>
            </div>

            <button class="delete-btn" onclick="deleteSelectedItems()">Delete Selected Items</button>
            <button class="export-btn" onclick="exportSelectedItems()">Export Selected Items to Inventory</button>
        </div>

        <!-- Add Manually Form Modal -->
        <div id="addItemModalForm" class="add-modal">
            <div class="add-modal-content">
                <h3>Add New Item</h3>

                <!-- Input Container -->
                <div class="input-container">
                    <input type="text" id="productName" placeholder="Product Name" required>
                    <input type="text" id="brand" placeholder="Brand" required>
                    <!--<input type="text" id="category" placeholder="Category" required>-->
                    <select id="category" class="category-dropdown" required>
                        <option value="" disabled selected>Select a category</option>
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
                    <input type="number" id="quantityNeeded" placeholder="Quantity" required>
                </div>

                <!-- Buttons -->
                <div class="button-container">
                    <button class="modal-submit-btn" onclick="addShopItem()">Submit</button>
                    <button class="modal-cancel-btn" onclick="closeItemForm()">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Previously Purchased Items Table (Initially Hidden) -->
        <div class="table-container hidden" id="purchasedItemsContainer">
            <table class="grocery-list-table">
                <thead>
                    <tr>
                        <th>Select</th>
                        <th onclick="sortTable(0, 'string', this)" data-order="asc">Product Name</th>
                        <th onclick="sortTable(1, 'string', this)" data-order="asc">Brand</th>
                        <th onclick="sortTable(2, 'string', this)" data-order="asc">Category</th>
                        <th onclick="sortTable(3, 'number', this)" data-order="asc">Quantity</th>
                    </tr>
                </thead>
                <tbody id="purchasesTableBody">
                    <!-- JavaScript will populate this -->
                </tbody>
            </table>
        </div>

        <!-- Buttons for Previously Purchased Items table -->
        <div class="bulk-actions hidden">
            <button id="restoreSelected" class="action-btn add-btn" onclick="restoreToShoppingList()">Restore to Shopping List</button>
            <button id="deleteSelected" class="action-btn delete-btn" onclick="deletePurchasedItems()">Remove from History</button>
        </div>
    </section>

    <script src="<? echo SITE_URL.'assets/js/ShopList.js'?>"></script>
    <script src="<? echo SITE_URL.'assets/js/Search.js'?>"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
</body>
</html>
