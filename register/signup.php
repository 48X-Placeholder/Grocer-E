<?php
// signup.php

// Include the global salt file
require_once __DIR__ . "/../config.php";

// Function to sanitize user inputs
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve user inputs
    $username = sanitize_input($_POST["username"]);
    $email = sanitize_input($_POST["email"]);
    $password = $_POST["password"]; // Password needs to be hashed, so sanitize later if needed for display
    $confirm_password = $_POST["confirm_password"]; // Confirm password for comparison


    // --- Input Validation ---

    // Check if username or email are empty
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: ../register"); // Redirect back to the registration form
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format.";
        header("Location: ../register");
        exit();
    }

    // Check if passwords match
    if ($password != $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match.";
        header("Location: ../register");
        exit();
    }

    // --- Database Operations ---

    // Create database connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_ACCOUNTS);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT UserId FROM users WHERE Username = ? OR Email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error_message'] = "Username or Email already taken.";
        $stmt->close();
        $conn->close();
        header("Location: ../register");
        exit();
    }
    $stmt->close();


    // Hash the password using password_hash with a salt
    $password_hash = password_hash($password . AUTH_SALT, PASSWORD_DEFAULT);

    // Prepare SQL statement to insert user data
    $stmt = $conn->prepare("INSERT INTO users (Username, Email, PasswordHash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password_hash);


    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Registration successful!";
        header("Location: ../register"); // Redirect to registration page with success message, or redirect to login page
    } else {
        $_SESSION['error_message'] = "Error during registration. Please try again.";
        // Log error for debugging: error_log("Signup error: " . $stmt->error);
        header("Location: ../register");
    }

    $stmt->close();
    $conn->close();
    exit();

} else {
    // If the form is not submitted via POST, redirect to the registration form
    header("Location: ../register");
    exit();
}
?>