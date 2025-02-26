<?php
require_once __DIR__ . "/../functions/load.php";

function site_navigation_menu()
{
    $loggedIn = cached_userid_info(); // Check if user_id is set, indicating login

    echo '
<nav class="Navigation-Menu">
    <a href="../" class="logo">
        <img src="../assets/images/grocer-e_text.png" alt="Grocer-E Logo">
        <span class="Business-Name">Grocer-E</span>
    </a>';
    if ($loggedIn) {
        echo "<h1>Hi " . htmlspecialchars(cached_username_info()) . "!</h1>";
    }
    echo '<div class="Navigation-Menu-Links">
        <a href="../">Home</a>';

    // Public links (always visible)
    echo '
        <a href="../about">About Us</a>';

    // Conditional links based on login status
    if ($loggedIn) {
        // Logged in user links
        echo '
        <a href="../dashboard">Dashboard</a>
        <a href="../inventory">Inventory List</a>
        <a href="../shopping-list">Shopping List</a>
        <a href="../account">Account</a>
        <a href="../logout">Signout</a>'; // Assuming you will create a logout.php
    } else {
        // Not logged in user links
        echo '
        <a href="../login">Login</a>
        <a href="../register">Register</a>';
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
                        <li><a href="../shopping-list">Shopping List</a></li>
                        <li><a href="../inventory">Inventory List</a></li>
                        <li><a href="../scan">Scan Items</a></li>
                        <li><a href="../account">Account</a></li>
                    </ul>
                </nav>
            </aside>
            ';
}
?>
