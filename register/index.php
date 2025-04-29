<?php
require_once __DIR__ . "/../page-templates/navigation-menu.php";
// Include the global salt file
require_once __DIR__ . "/../config.php";

if (is_user_logged_in()) {
	header("Location: ".SITE_URL.'dashboard'); // Redirect to dashboard
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

	// Process form submission only if it's a POST request
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		// Sanitize and retrieve inputs from the form
		$username = sanitize_input($_POST["username"]);
		$email = sanitize_input($_POST["email"]);
		$password = $_POST["password"];
		$confirm_password = $_POST["confirm_password"];

		// --- Input Validation ---
		if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
			$_SESSION["error_message"] = "All fields are required.";
		} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$_SESSION["error_message"] = "Invalid email format.";
		} elseif ($password != $confirm_password) {
			$_SESSION["error_message"] = "Passwords do not match.";
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
				$_SESSION["error_message"] = "Username or Email already taken.";
			} else {
				// Hash the password using password_hash for security
				$password_hash = password_hash($password . AUTH_SALT, PASSWORD_DEFAULT);

				// Insert the new user into the database
				$stmt = $conn->prepare("INSERT INTO users (Username, Email, PasswordHash) VALUES (?, ?, ?)");
				$stmt->bind_param("sss", $username, $email, $password_hash);

				if ($stmt->execute()) {
					// Registration successful
					$_SESSION["success_message"] = "Registration successful!";
				} else {
					// Registration failed
					$_SESSION["error_message"] = "Error during registration. Please try again.";
					// For debugging purposes, you can log the error:
					// error_log("Signup error: " . $stmt->error);
				}
			}
			$stmt->close();
			$conn->close();
		}

		// Redirect back to the registration form (register.php) to display messages
		header("Location: ".SITE_URL.'register');
		exit(); // Ensure that script execution stops after redirection
	}
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="<? echo SITE_URL.'assets/styles/signin.css'?>">
	<link rel="icon" type="image/png" href="<? echo SITE_URL.'assets/images/grocer-e_favicon.png'?>">
</head>
<body>
	<!-- Site Navigation -->
	<?php site_navigation_menu(); ?>
<div class="auth-container">
	<div class="auth-box">
        <h2>Register</h2>

        <?php
        if (isset($_SESSION["error_message"])) {
        	echo '<div class="error-message">' . $_SESSION["error_message"] . "</div>";
        	unset($_SESSION["error_message"]); // Clear the message after displaying
        }
        if (isset($_SESSION["success_message"])) {
        	echo '<div class="success-message">' . $_SESSION["success_message"] . "</div>";
        	unset($_SESSION["success_message"]); // Clear the message after displaying
        }
        ?>

         <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
             <div class="user-box">
                 <input type="text" name="username" required="">
                  <label>Username</label>
             </div>
             <div class="user-box">
                <input type="email" name="email" required="">
                <label>Email</label>
             </div>
             <div class="user-box">
                 <input type="password" name="password" required="">
                  <label>Password</label>
             </div>
             <div class="user-box">
             <input type="password" name="confirm_password" required="">
              <label>Confirm Password</label>
             </div>
             <button type="submit" class="button">Register</button>
         </form>
     </div>
    <div class="auth-image">
        <img src="<? echo SITE_URL.'assets/images/registerphoto1.jpg'?>" alt="Signup today">
    </div>
	
</body>
</html>
