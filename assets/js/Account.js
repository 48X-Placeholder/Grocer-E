/*document.addEventListener("DOMContentLoaded", function () {
    // Define the sections (Use correct IDs from navigation buttons)
    const sections = {
        "account-settings-btn": document.querySelector(".account-container"),
        "activity-logs-btn": document.querySelector(".activity-container"),
        "login-history-btn": document.querySelector(".login-container")  // Placeholder for login history section
    };

    // Ensure only Account Settings is visible by default
    Object.values(sections).forEach(section => {
        if (section) section.style.display = "none";
    });
    sections["account-settings-btn"].style.display = "block"; // Default section

    // Sidebar buttons: Switch sections when clicked
    document.querySelectorAll(".dashboard-sidebar nav ul li a").forEach(item => {
        item.addEventListener("click", function (event) {
            event.preventDefault(); // Prevent page reload

            // Get the section ID from the clicked button
            let selectedSection = this.id;

            // Hide all sections
            Object.values(sections).forEach(section => {
                if (section) section.style.display = "none";
            });

            // Show the selected section
            if (sections[selectedSection]) {
                sections[selectedSection].style.display = "block";

                // Load purchased items when viewing 'Previously Purchased Items'
                if (selectedSection === "previous-purchases-btn") {
                    fetchPurchasedItems();
                }
            }
        });
    });
});*/
