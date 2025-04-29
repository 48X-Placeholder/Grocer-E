<?php
require_once __DIR__ . "/../page-templates/navigation-menu.php";

CloseSessionReadOnly();
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Confirmation</title>
    <link rel="stylesheet" href="<? echo SITE_URL.'assets/styles/signin.css'?>">
	<link rel="icon" type="image/png" href="<? echo SITE_URL.'assets/images/grocer-e_favicon.png'?>">
    <style>
        .confirmation-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
			padding-top: 100px;
        }
		.confirmation-box {
			background: #328E6E
			padding: 30px;
			border-radius: 8px;
			box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
			text-align: center;
			width: 80%; /* Limits width to 80% of the screen */
			max-width: 500px; /* Prevents it from getting too wide */
			box-shadow: 0 15px 25px rgba(0,0,0,.6);
		}
        .confirmation-box h2 {
            color: white;
            margin-bottom: 20px;
        }
        .confirmation-box p {
            color: white;
            font-size: 1.1em;
            margin-bottom: 30px;
        }
        .redirect-message {
			color: white;
            font-size: 0.9em;
            color: #777;
        }
    </style>
    <script>
         setTimeout(function() {
            window.location.href = "<? echo SITE_URL.'login'?>"; // Redirect to login page
        }, 3000); // 3000 milliseconds = 3 seconds
    </script>
</head>
<body>
	<!-- Site Navigation -->
	<?php site_navigation_menu(); ?>

    <div class="confirmation-wrapper">
        <div class="auth-box confirmation-box">
            <h2>You have been signed out.</h2>
            <p>Thank you for visiting Grocer-E. You are now logged out of your account.</p>
            <p class="redirect-message">You will be redirected to the login page in a few seconds...</p>
        </div>
    </div>

</body>
</html>
