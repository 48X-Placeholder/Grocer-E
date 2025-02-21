<?php
require_once __DIR__ . "/../page-templates/navigation-menu.php";

require_once __DIR__ . "/../config.php";

// Function to sanitize inputs
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Process form submission if it's a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve inputs
    $usernameOrEmail = sanitize_input($_POST["usernameOrEmail"]); // Changed name to accept username or email
    $password = $_POST["password"];

    // --- Input Validation ---
    if (empty($usernameOrEmail) || empty($password)) {
        $_SESSION['error_message'] = "Username/Email and password are required.";
    } else {
        // --- Database Operations ---
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_ACCOUNTS);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Retrieve user data based on username or email
        $stmt = $conn->prepare("SELECT UserId, Username, Email, PasswordHash FROM users WHERE Username = ? OR Email = ?"); // Select necessary fields
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail); // Bind usernameOrEmail twice to check both columns
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc(); // Fetch user as associative array


        if ($user) {
            // Verify password
            if (password_verify($password . AUTH_SALT, $user['PasswordHash'])) {
                // --- Successful Login ---
                $_SESSION['user_id'] = $user['UserId']; // Store user ID in session (for persistent login)
                $_SESSION['username'] = $user['Username']; // Store username in session (optional, for display)
                $_SESSION['success_message'] = "Login successful!"; // Optional success message
                header("Location: ../dashboard"); // Redirect to dashboard
                exit(); // Ensure no further code is executed after redirect
            } else {
                $_SESSION['error_message'] = "Invalid password.";
            }
        } else {
            $_SESSION['error_message'] = "User not found.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../styles/signin.css">
</head>
<body>
	<!-- Site Navigation -->
	<?php site_navigation_menu(); ?>
    <div class="auth-box">
        <h2>Login</h2>

        <?php
            if (isset($_SESSION['error_message'])) {
                echo '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
                unset($_SESSION['error_message']);
            }
            if (isset($_SESSION['success_message'])) {
                echo '<div class="success-message">' . $_SESSION['success_message'] . '</div>';
                unset($_SESSION['success_message']);
            }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="user-box">
                <input type="text" name="usernameOrEmail" required="">                 <label>Username or Email</label>             </div>
            <div class="user-box">
                <input type="password" name="password" required="">                 <label>Password</label>
            </div>
            <button type="submit" class="button">Login</button>
        </form>
    </div>
    
</body>
</html>