<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

$host = "localhost"; // Your database host
$username = "u359724030_Brandyn"; // Your database username
$password = "ACKR|O#XV@2Fn]3f"; // Your database password

// Flag for dropping tables (fresh install)
$freshInstall = isset($_GET["reset"]) && $_GET["reset"] == 1; // Example: ?reset=1 in URL

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create grocery database
echo "Creating database grocery_db <br>";
if (!$conn->query("CREATE DATABASE IF NOT EXISTS u359724030_Brandyn")) {
    echo "MySQL Error creating grocery_db: " . $conn->error . "<br>";
}
$conn->select_db("u359724030_Brandyn");
echo "grocery_db selected <br>";

// Check if tables exist and drop if fresh install flag is set
if ($freshInstall) {
    $tables_grocery = ["local_products", "users", "inventory", "scan_logs", "shopping_list"];
    foreach ($tables_grocery as $table) {
        if (!$conn->query("DROP TABLE IF EXISTS " . $table)) {
            echo "MySQL Error dropping " . $table . ": " . $conn->error . "<br>";
        }
    }
}

// Create grocery tables
$sql = "CREATE TABLE IF NOT EXISTS local_products (
        ProductId INT AUTO_INCREMENT PRIMARY KEY,
        UserId INT NOT NULL,
        UPC VARCHAR(13) UNIQUE,
        ProductName VARCHAR(255) NOT NULL,
        Brand VARCHAR(255) DEFAULT NULL,
        Category VARCHAR(255) DEFAULT NULL,
        AddedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
if (!$conn->query($sql)) {
    echo "MySQL Error creating local_products: " . $conn->error . "<br>";
}
echo "local_products created <br>";

$sql = "CREATE TABLE IF NOT EXISTS users (
        UserId INT AUTO_INCREMENT PRIMARY KEY,
        Username VARCHAR(50) NOT NULL UNIQUE,
        Email VARCHAR(255) NOT NULL UNIQUE,
        PasswordHash VARCHAR(255) NOT NULL,
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
if (!$conn->query($sql)) {
    echo "MySQL Error creating grocery_db users: " . $conn->error . "<br>";
}
echo "grocery_db users created <br>";

$sql = "CREATE TABLE IF NOT EXISTS inventory (
        InventoryItemId INT AUTO_INCREMENT PRIMARY KEY,
        ProductId INT NOT NULL,
        UserId INT NOT NULL,
        Quantity INT NOT NULL,
        ExpirationDate DATE DEFAULT NULL,
        AddedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (ProductId) REFERENCES local_products(ProductId) ON DELETE CASCADE,
        FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
if (!$conn->query($sql)) {
    echo "MySQL Error creating inventory: " . $conn->error . "<br>";
}
echo "inventory created <br>";

$sql = "CREATE TABLE IF NOT EXISTS scan_logs (
        LogId INT AUTO_INCREMENT PRIMARY KEY,
        UserId INT NOT NULL,
        UPC VARCHAR(13) DEFAULT NULL,
        ActionType ENUM('ADD', 'REMOVE') DEFAULT NULL,
        AmountChanged INT DEFAULT NULL,
        Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        InventoryItemId INT DEFAULT NULL,
        FOREIGN KEY (InventoryItemId) REFERENCES inventory(InventoryItemId) ON DELETE SET NULL,
        FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
if (!$conn->query($sql)) {
    echo "MySQL Error creating scan_logs: " . $conn->error . "<br>";
}
echo "scan_logs created <br>";

$sql = "CREATE TABLE IF NOT EXISTS shopping_list (
        ListItemId INT AUTO_INCREMENT PRIMARY KEY,
        ProductId INT DEFAULT NULL,
        UserId INT NOT NULL,
        QuantityNeeded INT NOT NULL,
        AddedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        Purchased TINYINT(1) DEFAULT '0',
        FOREIGN KEY (ProductId) REFERENCES local_products(ProductId) ON DELETE CASCADE,
        FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
if (!$conn->query($sql)) {
    echo "MySQL Error creating shopping_list: " . $conn->error . "<br>";
}
echo "shopping_list created <br>";

// Create user database
echo "Creating database user_db <br>";
if (!$conn->query("CREATE DATABASE IF NOT EXISTS u359724030_Brandyn")) {
    echo "MySQL Error creating user_db: " . $conn->error . "<br>";
}
$conn->select_db("u359724030_Brandyn");
echo "user_db selected <br>";

// Check if tables exist and drop if fresh install flag is set
$tables_user = ["users", "login_history", "password_reset_tokens", "user_activity_logs", "user_preferences", "themes"];
if ($freshInstall) {
    foreach ($tables_user as $table) {
        if (!$conn->query("DROP TABLE IF EXISTS " . $table)) {
            echo "MySQL Error dropping " . $table . ": " . $conn->error . "<br>";
        }
    }
}

// Create user tables
$sql = "CREATE TABLE IF NOT EXISTS users (
        UserId INT AUTO_INCREMENT PRIMARY KEY,
        Username VARCHAR(50) NOT NULL UNIQUE,
        Email VARCHAR(255) NOT NULL UNIQUE,
        PasswordHash VARCHAR(255) NOT NULL,
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
if (!$conn->query($sql)) {
    echo "MySQL Error creating user_db users: " . $conn->error . "<br>";
}
echo "user_db users created <br>";

$sql = "CREATE TABLE IF NOT EXISTS login_history (
        LoginId INT AUTO_INCREMENT PRIMARY KEY,
        UserId INT NOT NULL,
        LoginTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        IPAddress VARCHAR(45) DEFAULT NULL,
        UserAgent TEXT,
        FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
    )";
if (!$conn->query($sql)) {
    echo "MySQL Error creating login_history: " . $conn->error . "<br>";
}
echo "login_history created <br>";

$sql = "CREATE TABLE IF NOT EXISTS password_reset_tokens (
        TokenId INT AUTO_INCREMENT PRIMARY KEY,
        UserId INT NOT NULL,
        ResetToken VARCHAR(255) NOT NULL,
        ExpiresAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
    )";
if (!$conn->query($sql)) {
    echo "MySQL Error creating password_reset_tokens: " . $conn->error . "<br>";
}
echo "password_reset_tokens created <br>";

$sql = "CREATE TABLE IF NOT EXISTS user_activity_logs (
        LogId INT AUTO_INCREMENT PRIMARY KEY,
        UserId INT NOT NULL,
        Action VARCHAR(255) NOT NULL,
        ActionTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
    )";
if (!$conn->query($sql)) {
    echo "MySQL Error creating user_activity_logs: " . $conn->error . "<br>";
}
echo "user_activity_logs created <br>";

$sql = "CREATE TABLE IF NOT EXISTS user_preferences (
        PreferenceId INT AUTO_INCREMENT PRIMARY KEY,
        UserId INT NOT NULL,
        ThemeId INT NOT NULL,
        NotificationsEnabled TINYINT(1) DEFAULT '1',
        ItemsPerPage INT DEFAULT '10',
        Language VARCHAR(10) DEFAULT 'en',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
    )";
if (!$conn->query($sql)) {
    echo "MySQL Error creating user_preferences: " . $conn->error;
}
echo "Database and tables creation process finished. Check for errors above.";
?>
