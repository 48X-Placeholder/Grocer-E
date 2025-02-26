<?php

require_once __DIR__ . "/../page-templates/navigation-menu.php";
require_once __DIR__ . "/../config.php";

// Redirect if user is not logged in
if (!is_user_logged_in()) {
    header("Location: ../login"); // Redirect to dashboard
    exit(); // Ensure no further code is executed after redirect
} else {
    // Function to sanitize inputs
    function sanitize_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $user_id = $_SESSION["user_id"]; // Get user ID from session
    $username = $_SESSION["username"]; // Get username from session (for display)

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_ACCOUNTS);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch current user email from database
    $stmt_email = $conn->prepare("SELECT Email FROM users WHERE UserId = ?");
    $stmt_email->bind_param("i", $user_id);
    $stmt_email->execute();
    $result_email = $stmt_email->get_result();
    $user_data = $result_email->fetch_assoc();
    $current_email = $user_data["Email"];
    $stmt_email->close();

    // --- Password Change Processing ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_password"])) {
        $old_password = $_POST["old_password"];
        $new_password = $_POST["new_password"];
        $confirm_new_password = $_POST["confirm_new_password"];

        if (empty($old_password) || empty($new_password) || empty($confirm_new_password)) {
            $_SESSION["account_error_message"] = "All password fields are required.";
        } elseif ($new_password != $confirm_new_password) {
            $_SESSION["account_error_message"] = "New passwords do not match.";
        } else {
            // Verify old password
            $stmt_verify_pass = $conn->prepare("SELECT PasswordHash FROM users WHERE UserId = ?");
            $stmt_verify_pass->bind_param("i", $user_id);
            $stmt_verify_pass->execute();
            $result_pass = $stmt_verify_pass->get_result();
            $user_pass_data = $result_pass->fetch_assoc();
            $hashed_password_from_db = $user_pass_data["PasswordHash"];
            $stmt_verify_pass->close();

            if (password_verify($old_password . AUTH_SALT, $hashed_password_from_db)) {
                // Hash new password
                $new_password_hash = password_hash($new_password . AUTH_SALT, PASSWORD_DEFAULT);
                // Update password in database
                $stmt_update_pass = $conn->prepare("UPDATE users SET PasswordHash = ? WHERE UserId = ?");
                $stmt_update_pass->bind_param("si", $new_password_hash, $user_id);
                if ($stmt_update_pass->execute()) {
                    $_SESSION["account_success_message"] = "Password updated successfully.";
                } else {
                    $_SESSION["account_error_message"] = "Error updating password. Please try again.";
                }
                $stmt_update_pass->close();
            } else {
                $_SESSION["account_error_message"] = "Incorrect old password.";
            }
        }
    }

    // --- Email Change Processing ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_email"])) {
        $new_email = sanitize_input($_POST["new_email"]);
        $confirm_new_email = sanitize_input($_POST["confirm_new_email"]);

        if (empty($new_email) || empty($confirm_new_email)) {
            $_SESSION["account_error_message"] = "All email fields are required.";
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION["account_error_message"] = "Invalid email format.";
        } elseif ($new_email != $confirm_new_email) {
            $_SESSION["account_error_message"] = "Emails do not match.";
        } else {
            // Check if new email is already taken
            $stmt_check_email = $conn->prepare("SELECT UserId FROM users WHERE Email = ?");
            $stmt_check_email->bind_param("s", $new_email);
            $stmt_check_email->execute();
            $stmt_check_email->store_result();

            if ($stmt_check_email->num_rows > 0) {
                $_SESSION["account_error_message"] = "This email is already taken.";
            } else {
                // Update email in database
                $stmt_update_email = $conn->prepare("UPDATE users SET Email = ? WHERE UserId = ?");
                $stmt_update_email->bind_param("si", $new_email, $user_id);
                if ($stmt_update_email->execute()) {
                    $_SESSION["account_success_message"] = "Email updated successfully.";
                    $_SESSION["username"] = $username; // Keep username in session
                    $current_email = $new_email; // Update current email for display
                } else {
                    $_SESSION["account_error_message"] = "Error updating email. Please try again.";
                }
                $stmt_update_email->close();
            }
            $stmt_check_email->close();
        }
    }

    $conn->close();
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <link rel="stylesheet" href="../assets/styles/signin.css">
    <link rel="stylesheet" href="../assets/styles/account.css">
</head>
<body>

    <?php site_navigation_menu(); ?>

    <div class="account-container auth-box">
        <h2>Account Settings</h2>
        <div class="user-details">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($current_email); ?></p>
        </div>

        <?php
        if (isset($_SESSION["account_error_message"])) {
            echo '<div class="error-message">' . $_SESSION["account_error_message"] . "</div>";
            unset($_SESSION["account_error_message"]);
        }
        if (isset($_SESSION["account_success_message"])) {
            echo '<div class="success-message">' . $_SESSION["account_success_message"] . "</div>";
            unset($_SESSION["account_success_message"]);
        }
        ?>

        <div class="settings-section">
            <h3>Change Password</h3>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="user-box">
                    <input type="password" name="old_password" required="">
                    <label>Current Password</label>
                </div>
                <div class="user-box">
                    <input type="password" name="new_password" required="">
                    <label>New Password</label>
                </div>
                <div class="user-box">
                    <input type="password" name="confirm_new_password" required="">
                    <label>Confirm New Password</label>
                </div>
                <button type="submit" class="button" name="change_password">Change Password</button>
            </form>
        </div>

        <div class="settings-section">
            <h3>Change Email</h3>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="user-box">
                    <input type="email" name="new_email" value="<?php echo htmlspecialchars(
                        $current_email
                    ); ?>" required="">
                    <label>New Email</label>
                </div>
                <div class="user-box">
                    <input type="email" name="confirm_new_email" required="">
                    <label>Confirm New Email</label>
                </div>
                <button type="submit" class="button" name="change_email">Change Email</button>
            </form>
        </div>
    </div>

</body>
</html>