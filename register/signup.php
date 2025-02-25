<?php
session_start(); // Start session to use session variables for messages

// Include the global salt file
require_once __DIR__ . "/../config.php";

// Function to sanitize inputs
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Process form submission only if it's a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve inputs from the form
    $username = sanitize_input($_POST["username"]);
    $email = sanitize_input($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // --- Input Validation ---
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format.";
    } elseif ($password != $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match.";
    } else {
        // --- Database Operations ---
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_ACCOUNTS);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if username or email already exists in the database
        $stmt = $conn->prepare("SELECT UserId FROM users WHERE Username = ? OR Email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $_SESSION['error_message'] = "Username or Email already taken.";
        } else {
            // Hash the password using password_hash for security
            $password_hash = password_hash($password . AUTH_SALT, PASSWORD_DEFAULT);

            // Insert the new user into the database
            $stmt = $conn->prepare("INSERT INTO users (Username, Email, PasswordHash) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password_hash);

            if ($stmt->execute()) {
                // Registration successful
                $_SESSION['success_message'] = "Registration successful!";
            } else {
                // Registration failed
                $_SESSION['error_message'] = "Error during registration. Please try again.";
                // For debugging purposes, you can log the error:
                // error_log("Signup error: " . $stmt->error);
            }
        }
        $stmt->close();
        $conn->close();
    }

    // Redirect back to the registration form (register.php) to display messages
    header("Location: ../register");
    exit(); // Ensure that script execution stops after redirection
} else {
    // If the form is not submitted via POST, redirect to the register page
    header("Location: ../register");
    exit();
}
?>
