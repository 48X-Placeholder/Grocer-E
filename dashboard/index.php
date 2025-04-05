<?php
require_once __DIR__ . "/../page-templates/navigation-menu.php";
require_once __DIR__ . "/../config.php";

if (!is_user_logged_in()) {
	header("Location: ".SITE_URL.'login');
	exit();
}

$username = cached_username_info();
$user_id = cached_userid_info();

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Count queries
$total_shopping_query = "SELECT COUNT(*) as total FROM shopping_list WHERE UserId = '$user_id' AND Purchased = '0'";
$total_shopping_result = $conn->query($total_shopping_query);
$total_shopping_count = $total_shopping_result->fetch_assoc()['total'] ?? 0;

$total_inventory_query = "SELECT COUNT(*) as total FROM inventory WHERE UserId = '$user_id'";
$total_inventory_result = $conn->query($total_inventory_query);
$total_inventory_count = $total_inventory_result->fetch_assoc()['total'] ?? 0;

// Preview queries
$shopping_query = "SELECT lp.ProductName, lp.Brand, lp.Category, sl.QuantityNeeded 
                   FROM shopping_list sl
                   JOIN local_products lp ON sl.ProductId = lp.ProductId
                   WHERE sl.UserId = '$user_id' AND sl.Purchased = '0'
                   ORDER BY sl.AddedAt ASC
                   LIMIT 5";
$shopping_result = $conn->query($shopping_query);

$inventory_query = "SELECT lp.ProductName, lp.Brand, lp.Category, i.Quantity, i.ExpirationDate 
                    FROM inventory i
                    JOIN local_products lp ON i.ProductId = lp.ProductId
                    WHERE i.UserId = '$user_id'
                    ORDER BY i.AddedAt ASC
                    LIMIT 5";
$inventory_result = $conn->query($inventory_query);

$conn->close();
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="<?php echo SITE_URL.'assets/styles/dashboard.css' ?>">
</head>
<body>
    <?php site_navigation_menu(true); ?>

    <div class="dashboard-container">
        <?php site_dashboard_sidebar_menu(); ?>

        <main class="dashboard-content">
            <h2>Welcome to Your Dashboard</h2>
            <p>Manage your shopping list and track inventory efficiently.</p>

            <!-- Shopping List Table -->
            <div class="table-container">
                <h2>Shopping List</h2>
                <table class="grocery-list-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>Quantity Needed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_shopping_count == 0): ?>
                            <tr>
                                <td colspan="4" class="faint-text">
                                    Your shopping list is currently empty. Visit the Shopping List page to start adding some!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($row = $shopping_result->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row["ProductName"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["Brand"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["Category"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["QuantityNeeded"]); ?></td>
                                </tr>
                            <?php } ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if ($total_shopping_count > 5): ?>
                    <p class="note-text">Showing 5 of <?php echo $total_shopping_count; ?> shopping list items</p>
                <?php endif; ?>
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
                    <tbody>
                        <?php if ($total_inventory_count == 0): ?>
                            <tr>
                                <td colspan="5" class="faint-text">
                                    Your inventory is currently empty. Visit the Inventory List page to add items and start tracking!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($row = $inventory_result->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row["ProductName"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["Brand"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["Category"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["Quantity"]); ?></td>
                                    <td>
                                        <?php 
                                            echo $row["ExpirationDate"] 
                                                ? htmlspecialchars($row["ExpirationDate"]) 
                                                : "-"; 
                                        ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if ($total_inventory_count > 5): ?>
                    <p class="note-text">Showing 5 of <?php echo $total_inventory_count; ?> inventory items</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
