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
    <section>
        <div class="text-section">
            <p>Grocer-E is designed to make grocery management easy and efficient. Whether you're keeping track of pantry stock or planning meals, our platform helps streamline your shopping experience.</p>
        </div>
        <div class="image-container">
        <img src="../assets/images/indexphoto2.jpg" alt="Child helping with groceries" class="full-width-image">
        </div>

        <div class="text-section">
            <p>With Grocer-E, you can log purchases, categorize items, and receive low-stock alerts. Say goodbye to forgotten groceries and last-minute store runs.</p>
        </div>
        <div class="image-container">
        <img src="../assets/images/indexphoto1.jpg" alt="Woman organizing pantry" class="full-width-image">
        </div>

        <div class="text-section">
            <p>Keep track of your fresh produce and avoid food waste. Our system ensures that your kitchen stays organized, and your meals are always planned ahead.</p>
        </div>
        <div class="image-container">
        <img src="../assets/images/indexphoto3.jpg" alt="Person placing vegetables in fridge" class="full-width-image">
        </div>
    </section>
	
	<footer class="footer">
        <a href="../about">About Us</a>
    </footer>
</body>
</html>