<?php
require_once __DIR__ . "/../page-templates/navigation-menu.php";
require_once __DIR__ . "/../config.php";

if (!is_user_logged_in()) {
	header("Location: ".SITE_URL.'login'); // Redirect to dashboard
	exit(); // Ensure no further code is executed after redirect
}

$username = cached_username_info();
$user_id = cached_userid_info(); // Get the logged-in user ID

// Connect to database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch shopping list items for the logged-in user
$shopping_query = "SELECT lp.ProductName, lp.Brand, lp.Category, sl.QuantityNeeded 
                   FROM shopping_list sl
                   JOIN local_products lp ON sl.ProductId = lp.ProductId
                   WHERE sl.UserId = '$user_id' AND sl.Purchased = '0'
                   ORDER BY sl.AddedAt ASC
                   LIMIT 5";
$shopping_result = $conn->query($shopping_query);

// Fetch inventory items for the logged-in user
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
    <link rel="stylesheet" href="<? echo SITE_URL.'assets/styles/dashboard.css'?>">
</head>
<body>
    <!-- Site Navigation -->
	<?php site_navigation_menu(true); ?>

     <div class="dashboard-container">
            <!-- Sidebar Navigation -->
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
                        <?php while ($row = $shopping_result->fetch_assoc()) { ?>
                          <tr>
                              <td><?php echo htmlspecialchars($row["ProductName"]); ?></td>
                              <td><?php echo htmlspecialchars($row["Brand"]); ?></td>
                              <td><?php echo htmlspecialchars($row["Category"]); ?></td>
                              <td><?php echo htmlspecialchars($row["QuantityNeeded"]); ?></td>
                           </tr>
                        <?php } ?>
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
                    <tbody>
                        <?php while ($row = $inventory_result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row["ProductName"]); ?></td>
                                <td><?php echo htmlspecialchars($row["Brand"]); ?></td>
                                <td><?php echo htmlspecialchars($row["Category"]); ?></td>
                                <td><?php echo htmlspecialchars($row["Quantity"]); ?></td>
                                <td><?php echo htmlspecialchars($row["ExpirationDate"]); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
