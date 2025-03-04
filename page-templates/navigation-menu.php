<?php
require_once __DIR__ . "/../functions/load.php";
require_once __DIR__ . "/../config.php";

function site_navigation_menu($dashboard = false)
{
    $loggedIn = cached_userid_info(); // Check if user_id is set, indicating login

    echo '
<nav class="Navigation-Menu">
    <a href="'.SITE_URL.'" class="logo">
        <img src="'.SITE_URL.'assets/images/grocer-e_text.png" alt="Grocer-E Logo">
    </a>';
    if ($loggedIn && $dashboard) {
        echo "<h1>Hi " . htmlspecialchars(cached_username_info()) . "!</h1>";
    }
    echo '<div class="Navigation-Menu-Links">
        <a href="'.SITE_URL.'">Home</a>';

    // Public links (always visible)
    echo '
        <a href="'.SITE_URL.'about">About Us</a>';

    // Conditional links based on login status
    if ($loggedIn) {
        // Logged in user links
        echo '
        <a href="'.SITE_URL.'dashboard">Dashboard</a>
        <a href="'.SITE_URL.'inventory">Inventory List</a>
        <a href="'.SITE_URL.'shopping-list">Shopping List</a>
        <a href="'.SITE_URL.'account">Account</a>
        <a href="'.SITE_URL.'logout">Signout</a>'; // Assuming you will create a logout.php
    } else {
        // Not logged in user links
        echo '
        <a href="'.SITE_URL.'login">Login</a>
        <a href="'.SITE_URL.'register">Register</a>';
    }

    echo '
    </div>
</nav>
';
}
function site_dashboard_sidebar_menu()
{
    echo '
    <aside class="dashboard-sidebar">
                <nav>
                    <ul>
                        <li><a href="'.SITE_URL.'shopping-list">Shopping List</a></li>
                        <li><a href="'.SITE_URL.'inventory">Inventory List</a></li>
                        <li><a href="'.SITE_URL.'account">Account</a></li>
                    </ul>
                </nav>
            </aside>
            ';
}
?>
