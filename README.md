# Grocer-E
A Complied Site, where development will take place for ease of use.

**Development Stack:** [XAMPP](https://www.apachefriends.org/) or a [Local WP](https://localwp.com/) (Modified WordPress Install)

**Setup:** Run ***db-setup.php*** or run the SQL Commands Below

***Notes**: If using ***db-setup.php***, if you want to reinstall the database (reset) then append **?reset=1** in URL
For Example: "grocer-e.local:10017/db-setup.php?reset=1"*

****here is sql code to build databases needed*****
**Create the grocery database**
```SQL
CREATE DATABASE IF NOT EXISTS grocery_db;
USE grocery_db;
```

**Create the Local Products table (Referenced in Inventory)**
```SQL
CREATE TABLE IF NOT EXISTS local_products (
    ProductId INT AUTO_INCREMENT PRIMARY KEY,
    UserId INT NOT NULL,
    UPC VARCHAR(13) UNIQUE,
    ProductName VARCHAR(255) NOT NULL,
    Brand VARCHAR(255) DEFAULT NULL,
    Category VARCHAR(255) DEFAULT NULL,
    AddedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

**Create the Users table (Referenced in Inventory)**
```SQL
CREATE TABLE IF NOT EXISTS users (
    UserId INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Email VARCHAR(255) NOT NULL UNIQUE,
    PasswordHash VARCHAR(255) NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

**Create the Inventory table after Local Products and Users exist**
```SQL
CREATE TABLE IF NOT EXISTS inventory (
    InventoryItemId INT AUTO_INCREMENT PRIMARY KEY,
    ProductId INT NOT NULL,
    UserId INT NOT NULL,
    Quantity INT NOT NULL,
    ExpirationDate DATE DEFAULT NULL,
    AddedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ProductId) REFERENCES local_products(ProductId) ON DELETE CASCADE,
    FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

**Create the Scan Logs table**
```SQL
CREATE TABLE IF NOT EXISTS scan_logs (
    LogId INT AUTO_INCREMENT PRIMARY KEY,
    UserId INT NOT NULL,
    UPC VARCHAR(13) DEFAULT NULL,
    ActionType ENUM('ADD', 'REMOVE') DEFAULT NULL,
    AmountChanged INT DEFAULT NULL,
    Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    InventoryItemId INT DEFAULT NULL,
    FOREIGN KEY (InventoryItemId) REFERENCES inventory(InventoryItemId) ON DELETE SET NULL,
    FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

**Create the Shopping List table**
```SQL
CREATE TABLE IF NOT EXISTS shopping_list (
    ListItemId INT AUTO_INCREMENT PRIMARY KEY,
    ProductId INT DEFAULT NULL,
    UserId INT NOT NULL,
    QuantityNeeded INT NOT NULL,
    AddedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Purchased TINYINT(1) DEFAULT '0',
    FOREIGN KEY (ProductId) REFERENCES local_products(ProductId) ON DELETE CASCADE,
    FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

**Create the user database**
```SQL
CREATE DATABASE IF NOT EXISTS user_db;
USE user_db;
```

**Create the Users table**
```SQL
CREATE TABLE IF NOT EXISTS users (
    UserId INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Email VARCHAR(255) NOT NULL UNIQUE,
    PasswordHash VARCHAR(255) NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Create the Login History table**
```SQL
CREATE TABLE IF NOT EXISTS login_history (
    LoginId INT AUTO_INCREMENT PRIMARY KEY,
    UserId INT NOT NULL,
    LoginTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    IPAddress VARCHAR(45) DEFAULT NULL,
    UserAgent TEXT,
    FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
);
```

**Create the Password Reset Tokens table**
```SQL
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    TokenId INT AUTO_INCREMENT PRIMARY KEY,
    UserId INT NOT NULL,
    ResetToken VARCHAR(255) NOT NULL,
    ExpiresAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
);
```

**Create the User Activity Logs table**
```SQL
CREATE TABLE IF NOT EXISTS user_activity_logs (
    LogId INT AUTO_INCREMENT PRIMARY KEY,
    UserId INT NOT NULL,
    Action VARCHAR(255) NOT NULL,
    ActionTimestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
);
```

**Create the User Preferences table**
```SQL
CREATE TABLE IF NOT EXISTS user_preferences (
    PreferenceId INT AUTO_INCREMENT PRIMARY KEY,
    UserId INT NOT NULL,
    ThemeId INT NOT NULL,
    NotificationsEnabled TINYINT(1) DEFAULT '1',
    ItemsPerPage INT DEFAULT '10',
    Language VARCHAR(10) DEFAULT 'en',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (UserId) REFERENCES users(UserId) ON DELETE CASCADE
);
```

**Create the Themes table**
```SQL
CREATE TABLE IF NOT EXISTS themes (
    ThemeId INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(50) DEFAULT 'LightMode',
    Description TEXT
);
```

***-Placeholder DATA-***

**Sample Data for Grocery DB**
```SQL
INSERT INTO grocery_db.local_products (UserId, UPC, ProductName, Brand, Category) VALUES
(1, '0123456789012', 'Milk', 'DairyBest', 'Dairy'),
(1, '0987654321098', 'Bread', 'Baker\'s Choice', 'Bakery'),
(2, '1234567890123', 'Eggs', 'FarmFresh', 'Dairy');
```

**Sample Data for User DB**
```SQL
INSERT INTO user_db.users (Username, Email, PasswordHash) VALUES
('testuser1', 'test1@example.com', 'hashedpassword1'),
('testuser2', 'test2@example.com', 'hashedpassword2');
```

****End****
