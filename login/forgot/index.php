<?php
require __DIR__ . "/../../page-templates/navigation-menu.php";
require __DIR__ . "/../../config.php";

if (is_user_logged_in()) {
	header("Location: ".SITE_URL.'dashboard'); // Redirect to dashboard
	exit(); // Ensure no further code is executed after redirect
}

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="<? echo SITE_URL.'assets/styles/signin.css';?>">
</head>
<body>

	<nav class="Navigation-Menu">
        <a href="<? echo SITE_URL ?>" class="logo">
            <img src="<? echo SITE_URL.'assets/images/Logo.png';?>" alt="Logo">
			<span class="Business-Name">Grocer-E</span>
        </a>

        <!-- Site Navigation -->
	<?php site_navigation_menu(); ?>
    </nav>
	<div class="auth-container">
	<div class="auth-box">
		<h2>Forgot Password</h2>
		<form>
			<div class="user-box">
				<input type="text" name="" required="">
				<label>Email</label>
			</div>
            
            <a href="#" class="button">Send Reset Link</a>
		</form>
	</div>
	</div>
</body>
</html>