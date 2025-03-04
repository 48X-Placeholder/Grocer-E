function sortTable(columnIndex, dataType, headerElement) {
    const table = document.querySelector(".grocery-list-table tbody");
    const rows = Array.from(table.rows);
    
    // Determine the current sort order and toggle it
    const currentOrder = headerElement.getAttribute("data-order");
    const newOrder = currentOrder === "asc" ? "desc" : "asc";
    headerElement.setAttribute("data-order", newOrder);

    // Remove sorting indicators from all headers
    document.querySelectorAll("th").forEach(th => th.classList.remove("asc", "desc"));

    // Add the sorting indicator to the clicked header
    headerElement.classList.add(newOrder);

    // Sort rows based on data type and order
    const sortedRows = rows.sort((a, b) => {
        let cellA = a.cells[columnIndex].innerText.trim();
        let cellB = b.cells[columnIndex].innerText.trim();

        if (dataType === 'number') {
            // Parse as integers, defaulting to 0 for non-numeric values
            const numA = parseInt(cellA) || 0;
            const numB = parseInt(cellB) || 0;
            return newOrder === "asc" ? numA - numB : numB - numA;
        } else if (dataType === 'date') {
            return newOrder === "asc" ? new Date(cellA) - new Date(cellB) : new Date(cellB) - new Date(cellA);
        } else {
            return newOrder === "asc" ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
        }
    });

    // Clear and re-append sorted rows
    table.innerHTML = "";
    sortedRows.forEach(row => table.appendChild(row));
}
