<?php
require_once __DIR__ . "/../page-templates/navigation-menu.php"; ?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../styles/dashboard.css">
</head>
<body>
<!-- Site Navigation -->
	<?php site_navigation_menu(); ?>
    <section class="list-section">
        <div class="tables-container">

            <!-- Shopping List Table -->
            <div class="table-container">
                <h2>Shopping List</h2>
                <table class="grocery-list-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody id="shoppingDashboardTable">
                        <tr><td colspan="4" style="text-align:center;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Inventory List Table -->
            <div class="table-container">
                <h2>Inventory List</h2>
                <table class="grocery-list-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Expiration Date</th>
                        </tr>
                    </thead>
                    <tbody id="inventoryDashboardTable">
                        <tr><td colspan="5" style="text-align:center;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Buttons to take users to respective full pages -->
        <div class="list-actions">
            <div><a class="shoplist-btn" href="../shopping-list/">View Full Shopping List</a></div>
            <div><a class="inventory-btn" href="../inventory/">View Full Inventory</a></div>
        </div>
    </section>  

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            fetch('fetch_dashboard_data.php')
                .then(response => response.json())
                .then(data => {
                    const shoppingTable = document.getElementById('shoppingDashboardTable');
                    shoppingTable.innerHTML = data.shoppingList.length
                        ? data.shoppingList.map(item => 
                            `<tr>
                                <td>${item.ProductName}</td>
                                <td>${item.Brand}</td>
                                <td>${item.Category}</td>
                                <td>${item.QuantityNeeded}</td>
                            </tr>`).join('')
                        : `<tr><td colspan="4" style="text-align:center;">No items in shopping list.</td></tr>`;

                    const inventoryTable = document.getElementById('inventoryDashboardTable');
                    inventoryTable.innerHTML = data.inventoryList.length
                        ? data.inventoryList.map(item => 
                            `<tr>
                                <td>${item.ProductName}</td>
                                <td>${item.Brand}</td>
                                <td>${item.Category}</td>
                                <td>${item.Quantity}</td>
                                <td>${item.ExpirationDate || 'N/A'}</td>
                            </tr>`).join('')
                        : `<tr><td colspan="5" style="text-align:center;">No items in inventory.</td></tr>`;
                })
                .catch(error => console.error('Error fetching dashboard data:', error));
        });
    </script>

</body>
</html>