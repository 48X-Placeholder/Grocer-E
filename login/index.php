<?php
require __DIR__ . "/../page-templates/navigation-menu.php";
require __DIR__ . "/../config.php";

// Function to sanitize inputs
function sanitize_input($data)
{
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}

if (is_user_logged_in()) {
	header("Location: ".SITE_URL.'dashboard'); // Redirect to dashboard
	exit(); // Ensure no further code is executed after redirect
} else {
	// Process form submission if it's a POST request
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
	//CAPTCHA Validation
		$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
		$verify = file_get_contents(
    		'https://www.google.com/recaptcha/api/siteverify'
  			. '?secret='   . RECAPTCHA_SECRET_KEY
  			. '&response=' . $recaptchaResponse
  			. '&remoteip=' . $_SERVER['REMOTE_ADDR']
		);
		$responseData = json_decode($verify);
		if (empty($responseData->success)) {
    		$_SESSION['error_message'] = 'Please complete the CAPTCHA.';
    		header('Location: ' . $_SERVER['PHP_SELF']);
    		exit;
}

		// Sanitize and retrieve inputs
		$usernameOrEmail = sanitize_input($_POST["usernameOrEmail"]); // Changed name to accept username or email
		$password = $_POST["password"];

		// --- Input Validation ---
		if (empty($usernameOrEmail) || empty($password)) {
			$_SESSION["error_message"] = "Username/Email and password are required.";
		} else {
			// --- Database Operations ---
			$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_ACCOUNTS);
			if ($conn->connect_error) {
				die("Connection failed: " . $conn->connect_error);
			}

			// Retrieve user data based on username or email
			$stmt = $conn->prepare(
				"SELECT UserId, Username, Email, PasswordHash FROM users WHERE Username = ? OR Email = ?"
			); // Select necessary fields
			$stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail); // Bind usernameOrEmail twice to check both columns
			$stmt->execute();
			$result = $stmt->get_result();
			$user = $result->fetch_assoc(); // Fetch user as associative array

			if ($user) {
				// Verify password
				if (password_verify($password . AUTH_SALT, $user["PasswordHash"])) {
					if (user_session_create($user["UserId"], $user["Username"])) {
						// --- Successful Login ---
						$_SESSION["success_message"] = "Login successful!"; // Optional success message
						header("Location: ".SITE_URL.'dashboard'); // Redirect to dashboard
						exit(); // Ensure no further code is executed after redirect
					}
				} else {
					$_SESSION["error_message"] = "Invalid password.";
				}
			} else {
				$_SESSION["error_message"] = "User not found.";
			}

			$stmt->close();
			$conn->close();
		}
	}
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="<? echo SITE_URL.'assets/styles/signin.css'?>">
	<link rel="icon" type="image/png" href="<? echo SITE_URL.'assets/images/grocer-e_favicon.png'?>">
</head>
<body>
	<!-- Site Navigation -->
	<?php site_navigation_menu(); ?>
	<div class="auth-container">
        <!-- Left Side: Login Form -->
        <div class="auth-box">
            <h2>Login</h2>
			<?php
				if (isset($_SESSION["error_message"])) {
					echo '<div class="error-message">' . $_SESSION["error_message"] . "</div>";
					unset($_SESSION["error_message"]);
				}
				if (isset($_SESSION["success_message"])) {
					echo '<div class="success-message">' . $_SESSION["success_message"] . "</div>";
					unset($_SESSION["success_message"]);
				}
			?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
				<div class="user-box">
					<input type="text" name="usernameOrEmail" required="">                 <label>Username or Email</label>             </div>
				<div class="user-box">
					<input type="password" name="password" required="">                 <label>Password</label>
				</div>
				
				<div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
				<script src="https://www.google.com/recaptcha/api.js" async defer></script>

				<button type="submit" class="button">Login</button>
				<a href="forgot/index.php" class="button">Forgot Password?</a>
			</form>
        </div>

        <!-- Right Side: Image -->
        <div class="auth-image">
            <img src="<? echo SITE_URL.'assets/images/loginphoto1.jpg'?>" alt="Shopping in Grocery Store">
        </div>
    </div>
</body>
</html>
