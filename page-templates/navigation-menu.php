<?php
session_start(); // Start session to access login status

function site_navigation_menu()
{
    $loggedIn = isset($_SESSION['user_id']); // Check if user_id is set, indicating login

    echo '
<nav class="Navigation-Menu">
    <a href="../" class="logo">
        <img src="../images/Logo.png" alt="Logo">
        <span class="Business-Name">Grocer-E</span>
    </a>
    <div class="Navigation-Menu-Links">
        <a href="../">Home</a>';

    // Public links (always visible)
    echo '
        <a href="../about_us">About Us</a>';

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
?>