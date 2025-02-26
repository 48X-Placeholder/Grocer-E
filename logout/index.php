<?php
require_once __DIR__ . "/../page-templates/navigation-menu.php";

CloseSessionReadOnly();
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Confirmation</title>
    <link rel="stylesheet" href="../assets/styles/signin.css">
    <style>
        .confirmation-box {
            background-color: #f4f4f4;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-top: 50px;
        }
        .confirmation-box h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .confirmation-box p {
            color: #666;
            font-size: 1.1em;
            margin-bottom: 30px;
        }
        .redirect-message {
            font-size: 0.9em;
            color: #777;
        }
    </style>
    <script>
        setTimeout(function() {
            window.location.href = '../login'; // Redirect to login page
        }, 3000); // 3000 milliseconds = 3 seconds
    </script>
</head>
<body>
	<!-- Site Navigation -->
	<?php site_navigation_menu(); ?>

    <div class="auth-box confirmation-box">
        <h2>You have been signed out.</h2>
        <p>Thank you for visiting Grocer-E. You are now logged out of your account.</p>
        <p class="redirect-message">You will be redirected to the login page in a few seconds...</p>
    </div>

</body>
</html>