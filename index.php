<?php
require_once dirname(__FILE__) . "/page-templates/navigation-menu.php"; ?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grocery Tracker</title>
    <link rel="stylesheet" href="../assets/styles/index.css">
</head>
<body>
	<!-- Site Navigation -->
	<?php site_navigation_menu(); ?>
	<section class="intro-section">
		<img src="../images/Generic1.jpg" alt="Generic Grocery Image" class="intro-image">
		<div class="intro-text-box">
			<p class="intro-text">
				The grocery tracking site is a streamlined tool designed to help users effortlessly manage
				their shopping lists and keep track of pantry items. With features to log purchases, monitor expiration dates,
				and categorize items by store or aisle, it simplifies meal planning and reduces food waste. Users can quickly
				search or scan items, receive reminders for low-stock essentials, and view past purchases to optimize future
				shopping. The interface is intuitive, with a dashboard that provides a quick overview of all pantry and fridge items at a glance.
			</p>
		</div>
	</section>
</body>
</html>