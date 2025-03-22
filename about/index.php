<?php
require __DIR__ . "/../page-templates/navigation-menu.php";

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="stylesheet" href="<? echo SITE_URL.'assets/styles/index.css'?>">
    <link rel="icon" type="image/png" href="<? echo SITE_URL.'assets/images/grocer-e_favicon.png'?>">
</head>
<body>
    <!-- Site Navigation -->
	<?php site_navigation_menu(); ?>
    <section>
        <h2>Meet Our Team</h2>
        <p>We were all a bunch of chill guys who met at CSUN through our program and wanted to come together to build
            something we could all be proud of. We all came together and built Grocer-E to give back to our community </p>
        
        <div class="image-container">
            <img src="<? echo SITE_URL.'assets/images/aboutusphoto1.jpg'?>" alt="Our Team" class="full-width-image">
        </div>

        <p>All participating students & or persons are not liable for anything, upon clicking on this site the user is
            entering upon their own risk. Batteries sold separately.</p>
    </section>

</body>
</html>