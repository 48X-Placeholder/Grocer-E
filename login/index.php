<?php
require_once __DIR__ . "/../page-templates/navigation-menu.php"; ?>
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
		<form>
			<div class="user-box">
				<input type="text" name="" required="">
				<label>Username</label>
			</div>
			<div class="user-box">
				<input type="password" name="" required="">
				<label>Password</label>
			</div>
			            <!-- Add a login button -->
            <a href="#" class="button">Login</a>
            <a href="../register" class="button">Register</a>
			<a href="../forgotpass.html" class="button">Forgot Password</a>
		</form>
	</div>
	
</body>
</html>
