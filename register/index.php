<?php
require_once __DIR__ . "/../page-templates/navigation-menu.php"; ?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../styles/signin.css">
</head>
<body>
	<!-- Site Navigation -->
	<?php site_navigation_menu(); ?>
	
	<div class="auth-box">
        <h2>Register</h2>

        <?php
            if (isset($_SESSION['error_message'])) {
                echo '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
                unset($_SESSION['error_message']); // Clear the message after displaying
            }
            if (isset($_SESSION['success_message'])) {
                echo '<div class="success-message">' . $_SESSION['success_message'] . '</div>';
                unset($_SESSION['success_message']); // Clear the message after displaying
            }
        ?>

        <form action="signup.php" method="POST">             <div class="user-box">
                <input type="text" name="username" required="">                 <label>Username</label>
            </div>
            <div class="user-box">
                <input type="email" name="email" required="">                 <label>Email</label>
            </div>
            <div class="user-box">
                <input type="password" name="password" required="">                 <label>Password</label>
            </div>
            <div class="user-box">
                <input type="password" name="confirm_password" required="">                 <label>Confirm Password</label>
            </div>
            <button type="submit" class="button">Register</button>         </form>
    </div>
	
</body>
</html>