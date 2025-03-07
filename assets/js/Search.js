// Function to allow searching within list tables
function handleSearch() {
    const searchQuery = document.getElementById('list-search').value.trim().toLowerCase();
    const rows = document.querySelectorAll("#groceryTableBody tr, #shoppingTableBody tr"); // Select both tables

    rows.forEach(row => {
        const productName = row.querySelector("td:nth-child(2)")?.innerText.toLowerCase() || "";
        const brand = row.querySelector("td:nth-child(3)")?.innerText.toLowerCase() || "";
        const category = row.querySelector("td:nth-child(4)")?.innerText.toLowerCase() || "";
        
        // Show all rows again if search is cleared
        if (searchQuery === "") {
            row.style.display = "";
            return;
        }

        // Hide rows that don't match search
        if (productName.includes(searchQuery) || brand.includes(searchQuery) || category.includes(searchQuery)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}

// Function to clear the search field and reset the table
function clearSearch() {
    document.getElementById('list-search').value = "";
    handleSearch(); // Calls search again to reset the table
}
