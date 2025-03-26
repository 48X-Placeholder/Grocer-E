document.addEventListener("DOMContentLoaded", function () {
    // Map buttons to their corresponding sections
    const sections = {
        "account-settings-btn": document.querySelector(".account-container"),
        "activity-logs-btn": document.querySelector(".activity-container"),
        "login-history-btn": document.querySelector(".login-container")
    };

    // Hide all sections initially
    Object.values(sections).forEach(section => {
        if (section) section.style.display = "none";
    });

    // Show the default section (Account Settings)
    const defaultSectionId = "account-settings-btn";
    if (sections[defaultSectionId]) {
        sections[defaultSectionId].style.display = "block";
    }

    // Handle sidebar navigation clicks
    document.querySelectorAll(".dashboard-sidebar nav ul li a").forEach(link => {
        link.addEventListener("click", function (event) {
            event.preventDefault();

            const clickedId = this.id;

            // Hide all sections
            Object.values(sections).forEach(section => {
                if (section) section.style.display = "none";
            });

            // Show the selected section
            if (sections[clickedId]) {
                sections[clickedId].style.display = "block";
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    loadActivityLogs(); // Ensure this is executed when the page loads
});

function loadActivityLogs() {
    fetch('../api/account/fetch_activity_logs/', {
        method: 'GET',
        redirect: 'follow',
        referrerPolicy: 'no-referrer'
    })
    .then(response => response.json())
    .then(result => {
        const tableBody = document.getElementById('activityLogsTableBody');
        tableBody.innerHTML = ''; // Clear existing table content

        if (!result.success || result.logs.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="3" style="text-align: center; font-style: italic; color: #888;">
                        No activity logs found.
                    </td>
                </tr>
            `;
            return;
        }

        result.logs.forEach(log => {
            let actionType = log.LogSource === "scan" ? log.ActionType : log.Action;
            let details = log.LogSource === "scan"
                ? `UPC: ${log.UPC}, ${log.ActionType}, Amount: ${log.AmountChanged}`
                : "User Activity";

            const row = `
                <tr>
                    <td>${formatDate(log.ActionTimestamp)}</td>  <!-- FIX: Timestamp First -->
                    <td>${actionType}</td>  <!-- FIX: Action Type -->
                    <td>${details}</td>  <!-- FIX: Details Last -->
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', row);
        });
    })
    .catch(error => console.error('Error fetching activity logs:', error));
}

// Helper function to format timestamps
function formatDate(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleString(); // Formats as readable date/time
}
