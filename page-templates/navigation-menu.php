<?php
function site_navigation_menu()
{
    echo '
<nav class="Navigation-Menu">
    <a href="../" class="logo">
        <img src="../images/Logo.png" alt="Logo">
        <span class="Business-Name">Grocer-E</span>
    </a>
    <div class="Navigation-Menu-Links">
        <a href="../">Home</a>
        <!-- TEMPORARY, delete after user login is implemented -->
        <a href="../dashboard">Dashboard</a>
        <a href="../about_us">About Us</a>
        <a href="../inventory">Inventory List</a>
        <a href="../shopping-list">Shopping List</a>
        <a href="../login">Login/Register</a>
    </div>
</nav>
';
}
?>
