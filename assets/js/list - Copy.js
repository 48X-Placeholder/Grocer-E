/*
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
*/

// JavaScript function to load data from the PHP script
function loadGroceryData() {
    fetch('fetch_inventory.php')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('groceryTableBody');
            tableBody.innerHTML = ''; // Clear any existing content

            data.forEach(item => {
                const row = `
                    <tr>
                        <td><input type="checkbox" class="delete-checkbox" data-id="${item.ProductId}"></td>
                        <td>${item.ProductName}</td>
                        <td>${item.Brand}</td>
                        <td>${item.Category}</td>
                        <td>${item.Quantity}</td>
                        <td>${item.ExpirationDate}</td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML('beforeend', row);
            });
        })
        .catch(error => console.error('Error fetching data:', error));
}

// Add item function
function addItem() {
    const productName = document.getElementById('productName').value;
    const brand = document.getElementById('brand').value;
    const category = document.getElementById('category').value;
    const quantity = document.getElementById('quantity').value;
    const expirationDate = document.getElementById('expirationDate').value;

    const itemData = {
        ProductName: productName,
        Brand: brand,
        Category: category,
        Quantity: quantity,
        ExpirationDate: expirationDate
    };

    fetch('add_item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(itemData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Item added successfully!');
            loadGroceryData(); // Refresh the list after adding
            cancelAddItem(); // Hide the form after adding
        } else {
            alert('Failed to add item!');
        }
    })
    .catch(error => console.error('Error adding item:', error));
}

// Delete selected items function
function deleteSelectedItems() {
    const selectedItems = [];
    const checkboxes = document.querySelectorAll('.delete-checkbox:checked');
    checkboxes.forEach(checkbox => {
        selectedItems.push(checkbox.getAttribute('data-id'));
    });

    if (selectedItems.length === 0) {
        alert('Please select at least one item to delete.');
        return;
    }

    fetch('delete_item.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ itemIds: selectedItems })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Items deleted successfully!');
            loadGroceryData(); // Refresh the list after deleting
        } else {
            alert('Failed to delete items!');
        }
    })
    .catch(error => console.error('Error deleting items:', error));
}

// Show the add item form
function toggleAddItemForm() {
    const form = document.getElementById('addItemForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

// Cancel the add item form (hide and reset)
function cancelAddItem() {
    const form = document.getElementById('addItemForm');
    form.style.display = 'none'; // Hide the form
    document.getElementById('productName').value = '';
    document.getElementById('brand').value = '';
    document.getElementById('category').value = '';
    document.getElementById('quantity').value = '';
    document.getElementById('expirationDate').value = '';
}

// Load data when the page loads
document.addEventListener('DOMContentLoaded', loadGroceryData);