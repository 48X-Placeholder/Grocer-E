<?php
require_once dirname(__FILE__) . "/../../page-templates/navigation-menu.php";
require_once dirname(__FILE__) . '../../../config.php';

if (is_user_logged_in()) {
	header("Location: " . SITE_URL . 'dashboard');
	exit;
}

// Check token in query string
$token = $_GET['token'] ?? '';
$valid = false;
$userId = null;

if ($token) {
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME_ACCOUNTS);
	if (!$conn->connect_error) {
		$stmt = $conn->prepare("SELECT UserId, ExpiresAt FROM password_reset_tokens WHERE ResetToken = ?");
		$stmt->bind_param("s", $token);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_assoc();
		$stmt->close();

		if ($row && strtotime($row['ExpiresAt']) > time()) {
			$valid = true;
			$userId = $row['UserId'];
		}
	}
}

// Handle password update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["password"]) && $valid && $userId) {
	$newPassword = $_POST["password"];
	$hashed = password_hash($newPassword . AUTH_SALT, PASSWORD_DEFAULT);

	$stmt = $conn->prepare("UPDATE users SET PasswordHash = ? WHERE UserId = ?");
	$stmt->bind_param("si", $hashed, $userId);
	$stmt->execute();
	$stmt->close();

	// Delete the token
	$conn->query("DELETE FROM password_reset_tokens WHERE UserId = $userId");

	$conn->close();

	$_SESSION["success_message"] = "Password reset successfully. You can now log in.";
	header("Location: " . SITE_URL . "login");
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title>Reset Password</title>
	<link rel="stylesheet" href="<?php echo SITE_URL . 'assets/styles/signin.css'; ?>">
	<link rel="icon" type="image/png" href="<? echo SITE_URL.'assets/images/grocer-e_favicon.png'?>">
</head>
<body>
	<?php site_navigation_menu(); ?>
	<div class="auth-container">
		<div class="auth-box">
			<h2>Reset Password</h2>

			<?php if (!$valid): ?>
				<div class="error-message">
					Invalid or expired token. Please try the password reset process again.
				</div>
			<?php else: ?>
				<form method="POST">
					<div class="user-box">
						<input type="password" name="password" required>
						<label>New Password</label>
					</div>
					<button type="submit" class="button">Reset Password</button>
				</form>
			<?php endif; ?>
		</div>
	</div>
</body>
</html>
