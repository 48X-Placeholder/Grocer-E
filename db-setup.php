<?php
require_once __DIR__ . 'config.php'; // Ensure correct database connection

// Flag for dropping tables (fresh install)
$freshInstall = isset($_GET['reset']) && $_GET['reset'] == 1; // Example: ?reset=1 in URL

try {
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Create grocery database
    $conn->query("CREATE DATABASE IF NOT EXISTS grocery_db");
    $conn->select_db("grocery_db");

    // Check if tables exist and drop if fresh install flag is set
    $tables_grocery = ["local_products", "users", "inventory", "scan_logs", "shopping_list"];
    if ($freshInstall) {
        foreach ($tables_grocery as $table) {
            $conn->query("DROP TABLE IF EXISTS " . $table);
        }
    }

    // Create grocery tables
    $conn->query("CREATE TABLE IF NOT EXISTS local_products (
        ProductId INT AUTO_INCREMENT PRIMARY KEY,
        UserId INT NOT NULL,
        UPC VARCHAR(13) UNIQUE,
        ProductName VARCHAR(255) NOT NULL,
        Brand VARCHAR(255) DEFAULT NULL,
        Category VARCHAR(255) DEFAULT NULL,
        AddedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    $conn->query("CREATE TABLE IF NOT EXISTS users (
        UserId INT AUTO_INCREMENT PRIMARY KEY,
        Username VARCHAR(50) NOT NULL UNIQUE,
        Email VARCHAR(255) NOT NULL UNIQUE,
        PasswordHash VARCHAR(255) NOT NULL,
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    $conn->query("CREATE TABLE IF NOT EXISTS inventory (
        InventoryItemId INT AUTO_INCREMENT PRIMARY KEY,
        ProductId INT NOT NULL,
        UserId INT NOT NULL,
        Quantity INT NOT NULL,
        ExpirationDate DATE DEFAULT NULL,
        AddedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (ProductId) REFERENCES local_products(ProductId) ON DELETE CASCADE,
        FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    $conn->query("CREATE TABLE IF NOT EXISTS scan_logs (
        LogId INT AUTO_INCREMENT PRIMARY KEY,
        UserId INT NOT NULL,
        UPC VARCHAR(13) DEFAULT NULL,
        ActionType ENUM('ADD', 'REMOVE') DEFAULT NULL,
        AmountChanged INT DEFAULT NULL,
        Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        InventoryItemId INT DEFAULT NULL,
        FOREIGN KEY (InventoryItemId) REFERENCES inventory(InventoryItemId) ON DELETE SET NULL,
        FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    $conn->query("CREATE TABLE IF NOT EXISTS shopping_list (
        ListItemId INT AUTO_INCREMENT PRIMARY KEY,
        ProductId INT DEFAULT NULL,
        UserId INT NOT NULL,
        QuantityNeeded INT NOT NULL,
        AddedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        Purchased TINYINT(1) DEFAULT '0',
        FOREIGN KEY (ProductId) REFERENCES local_products(ProductId) ON DELETE CASCADE,
        FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");


    // Create user database
    $conn->query("CREATE DATABASE IF NOT EXISTS user_db");
    $conn->select_db("user_db");

    // Check if tables exist and drop if fresh install flag is set
    $tables_user = ["users", "login_history", "password_reset_tokens", "user_activity_logs", "user_preferences", "themes"];
    if ($freshInstall) {
        foreach ($tables_user as $table) {
            $conn->query("DROP TABLE IF EXISTS " . $table);
        }
    }

    // Create user tables
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        UserId INT AUTO_INCREMENT PRIMARY KEY,
        Username VARCHAR(50) NOT NULL UNIQUE,
        Email VARCHAR(255) NOT NULL UNIQUE,
        PasswordHash VARCHAR(255) NOT NULL,
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS login_history (
        LoginId INT AUTO_INCREMENT PRIMARY KEY,
        UserId INT NOT NULL,
        LoginTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        IPAddress VARCHAR(45) DEFAULT NULL,
        UserAgent TEXT,
        FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS password_reset_tokens (
        TokenId INT AUTO_INCREMENT PRIMARY KEY,
        UserId INT NOT NULL,
        ResetToken VARCHAR(255) NOT NULL,
        ExpiresAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS user_activity_logs (
        LogId INT AUTO_INCREMENT PRIMARY KEY,
        UserId INT NOT NULL,
        Action VARCHAR(255) NOT NULL,
        ActionTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS user_preferences (
        PreferenceId INT AUTO_INCREMENT PRIMARY KEY,
        UserId INT NOT NULL,
        ThemeId INT NOT NULL,
        NotificationsEnabled TINYINT(1) DEFAULT '1',
        ItemsPerPage INT DEFAULT '10',
        Language VARCHAR(10) DEFAULT 'en',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS themes (
        ThemeId INT AUTO_INCREMENT PRIMARY KEY,
        Name VARCHAR(50) DEFAULT 'LightMode',
        Description TEXT
    )");


    // Insert sample data (grocery_db)
    $conn->select_db("grocery_db");
    $conn->query("INSERT INTO local_products (UserId, UPC, ProductName, Brand, Category) VALUES
        (1, '0123456789012', 'Milk', 'DairyBest', 'Dairy'),
        (1, '0987654321098', 'Bread', 'Baker\'s Choice', 'Bakery'),
        (2, '1234567890123', 'Eggs', 'FarmFresh', 'Dairy')");

    // Insert sample data (user_db)
    $conn->select_db("user_db");
    $conn->query("INSERT INTO users (Username, Email, PasswordHash) VALUES
        ('testuser1', 'test1@example.com', 'hashedpassword1'),
        ('testuser2', 'test2@example.com', 'hashedpassword2')");


    echo "Database and tables created successfully!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    $conn->close();
}

?>