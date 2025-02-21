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
		<form>
			<div class="user-box">
				<input type="text" name="" required="">
				<label>Username</label>
			</div>
			<div class="user-box">
				<input type="text" name="" required="">
				<label>Email</label>
			</div>
			<div class="user-box">
				<input type="password" name="" required="">
				<label>Password</label>
			</div>
			<div class="user-box">
				<input type="password" name="" required="">
				<label>Confirm Password</label>
			</div>
            <a href="#" class="button">Register</a>
		</form>
	</div>
	
</body>
</html>
