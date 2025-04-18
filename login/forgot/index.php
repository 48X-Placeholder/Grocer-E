<?php
require __DIR__ . "/../../page-templates/navigation-menu.php";
require __DIR__ . "/../../config.php";

// Autoload PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../functions/phpmailer/src/Exception.php';
require_once __DIR__ . '/../../functions/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../functions/phpmailer/src/SMTP.php';

// Redirect logged-in users
if (is_user_logged_in()) {
	header("Location: " . SITE_URL . 'dashboard');
	exit;
}

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['email'])) {
	$email = trim($_POST['email']);
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_ACCOUNTS);

	if ($conn->connect_error) {
		die("Database connection failed");
	}

	// Look up user by email
	$stmt = $conn->prepare("SELECT UserId, Username FROM users WHERE Email = ?");
	$stmt->bind_param("s", $email);
	$stmt->execute();
	$result = $stmt->get_result();
	$user = $result->fetch_assoc();
	$stmt->close();

	if ($user) {
		$userId = $user['UserId'];
		$token = bin2hex(random_bytes(32));
		$expiresAt = date("Y-m-d H:i:s", strtotime("+30 minutes"));

		// Delete old token(s)
		$conn->query("DELETE FROM password_reset_tokens WHERE UserId = $userId");

		// Store new token
		$insert = $conn->prepare("INSERT INTO password_reset_tokens (UserId, ResetToken, ExpiresAt) VALUES (?, ?, ?)");
		$insert->bind_param("iss", $userId, $token, $expiresAt);
		$insert->execute();
		$insert->close();

		// Build reset link
		$reset_link = SITE_URL . "login/reset_password/?token=" . urlencode($token);

		// Send email using PHPMailer
		$mail = new PHPMailer(true);
		try {
			$mail->isSMTP();
			$mail->Host = 'smtp.gmail.com';
			$mail->SMTPAuth = true;

			// credentials for the mail server (MAKE SECURE)
			$mail->Username = EMAIL_FROM_ADDRESS;
			$mail->Password = EMAIL_APP_PASSWORD;
			$mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
			$mail->addReplyTo(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);


			$mail->SMTPSecure = 'tls';
			$mail->Port = 587;

			$mail->setFrom('your_email@gmail.com', 'Grocer-E');
			$mail->addAddress($email);
			$mail->Subject = 'Password Reset Request';
			$mail->Body = "Hello,\n\nWe received a request to reset your password. Click the link below to reset it:\n\n$reset_link\n\nThis link will expire in 30 minutes.\n\nIf you did not request this, you can safely ignore this email.\n\nGrocer-E";

			$mail->send();
		} catch (Exception $e) {
			error_log("PHPMailer Error: " . $mail->ErrorInfo); // Optional debug logging
		}
	}

	$conn->close();

	// Always show a generic success message
	$_SESSION["success_message"] = "If an account with that email exists, a reset link has been sent.";
	header("Location: " . SITE_URL . "login/forgot/index.php");
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title>Forgot Password</title>
	<link rel="stylesheet" href="<?php echo SITE_URL . 'assets/styles/signin.css'; ?>">
</head>
<body>
	<nav class="Navigation-Menu">
		<a href="<?php echo SITE_URL ?>" class="logo">
			<img src="<?php echo SITE_URL . 'assets/images/Logo.png'; ?>" alt="Logo">
			<span class="Business-Name">Grocer-E</span>
		</a>
		<?php site_navigation_menu(); ?>
	</nav>

	<div class="auth-container">
		<div class="auth-box">
			<h2>Forgot Password</h2>

			<?php
			if (isset($_SESSION["success_message"])) {
				echo '<div class="success-message">' . $_SESSION["success_message"] . "</div>";
				unset($_SESSION["success_message"]);
			}
			?>

			<form action="" method="POST">
				<div class="user-box">
					<input type="email" name="email" required>
					<label>Email Address</label>
				</div>
				<button type="submit" class="button">Send Reset Link</button>
			</form>
		</div>
	</div>
</body>
</html>
