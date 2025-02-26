function sortTable(columnIndex, dataType, headerElement) {
    const tableBody = document.getElementById("groceryTableBody");
    const rows = Array.from(tableBody.rows);

    // Adjust column index to account for the "Select" column at index 0
    const adjustedColumnIndex = columnIndex + 1;  

    // Determine current sort order and toggle it
    const currentOrder = headerElement.getAttribute("data-order");
    const newOrder = currentOrder === "asc" ? "desc" : "asc";
    headerElement.setAttribute("data-order", newOrder);

    // Remove sorting indicators from all headers
    document.querySelectorAll("th").forEach(th => th.classList.remove("asc", "desc"));

    // Apply normal sorting direction (undoing previous fix)
    headerElement.classList.add(newOrder);

    // Sort rows based on data type and order
    const sortedRows = rows.sort((a, b) => {
        let cellA = a.cells[adjustedColumnIndex].innerText.trim();
        let cellB = b.cells[adjustedColumnIndex].innerText.trim();

        if (dataType === "number") {
            const numA = parseFloat(cellA) || 0;
            const numB = parseFloat(cellB) || 0;
            return newOrder === "asc" ? numA - numB : numB - numA;
        } else if (dataType === "date") {
            const dateA = Date.parse(cellA) || 0;
            const dateB = Date.parse(cellB) || 0;
            return newOrder === "asc" ? dateA - dateB : dateB - dateA;
        } else {
            return newOrder === "asc"
                ? cellA.localeCompare(cellB)
                : cellB.localeCompare(cellA);
        }
    });

    // Clear and re-append sorted rows
    tableBody.innerHTML = "";
    sortedRows.forEach(row => tableBody.appendChild(row));
}

// JavaScript function to load data from the PHP script
function loadGroceryData() {
    fetch('../inventory/fetch_inventory.php')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('groceryTableBody');
            tableBody.innerHTML = ''; // Clear existing table contents

            if (data.length === 0) {
                // If no items exist, display a message row
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align: center; font-style: italic; color: #888;">
                            No items in inventory. Add some groceries to get started!
                        </td>
                    </tr>
                `;
                return; // Exit function early since there's nothing else to add
            }

            // Populate the table with inventory items
            data.forEach(item => {
                const row = `
                    <tr>
                        <td><input type="checkbox" class="item-checkbox" data-id="${item.InventoryItemId}"></td>
                        <td>${item.ProductName}</td>
                        <td>${item.Brand}</td>
                        <td>${item.Category}</td>
                        <td>${item.Quantity}</td>
                        <td>${item.ExpirationDate || 'N/A'}</td>
                        <td>
                            <button onclick="editItem(${item.InventoryItemId}, '${item.ProductName}', '${item.Brand}', '${item.Category}', ${item.Quantity}, '${item.ExpirationDate || ''}')">
                                Edit
                            </button>
                        </td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML('beforeend', row);
            });
        })
        .catch(error => console.error('Error fetching data:', error));
}

// Add item function
function addItem() {
    const upcCode = document.getElementById('upcCode').value.trim();
    const productName = document.getElementById('productName').value.trim();
    const brand = document.getElementById('brand').value.trim();
    const category = document.getElementById('category').value.trim();
    const quantity = parseInt(document.getElementById('quantity').value);
    const expirationDate = document.getElementById('expirationDate').value;

    const requestData = { upcCode, productName, brand, category, quantity, expirationDate };

    fetch('../inventory/add_inventory_item.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            loadGroceryData();
            cancelAddItem();
            alert('Item added successfully!');
        } else {
            alert('Error adding item: ' + result.message);
        }
    })
    .catch(error => console.error('Error adding item:', error));
}

// delete selected item(s) function
function deleteSelectedItems() {
    const selectedIds = Array.from(document.querySelectorAll('.item-checkbox:checked'))
        .map(checkbox => checkbox.getAttribute('data-id'));

    console.log("Selected IDs for deletion:", selectedIds); // Debugging

    if (selectedIds.length === 0) {
        alert('Please select at least one item to delete.');
        return;
    }

    fetch('../inventory/delete_inventory_item.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ itemIds: selectedIds })
    })
    .then(response => response.json())
    .then(result => {
        console.log("Response from server:", result); // Debugging
        if (result.success) {
            loadGroceryData();
            alert('Selected items deleted successfully!');
        } else {
            alert('Error deleting items: ' + result.message);
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
    
    // Reset all form fields
    document.getElementById('upcCode').value = '';
    document.getElementById('productName').value = '';
    document.getElementById('brand').value = '';
    document.getElementById('category').value = '';
    document.getElementById('quantity').value = '';
    document.getElementById('expirationDate').value = '';
}

// 
function editItem(id, name, brand, category, quantity, expiration) {
    document.getElementById('editItemId').value = id;
    document.getElementById('editProductName').value = name;
    document.getElementById('editBrand').value = brand;
    document.getElementById('editCategory').value = category;
    document.getElementById('editQuantity').value = quantity;
    document.getElementById('editExpirationDate').value = expiration;

    document.getElementById('editItemForm').style.display = 'block';
}

// 
function updateItem() {
    const itemId = document.getElementById('editItemId').value;
    const productName = document.getElementById('editProductName').value.trim();
    const brand = document.getElementById('editBrand').value.trim();
    const category = document.getElementById('editCategory').value.trim();
    const quantity = parseInt(document.getElementById('editQuantity').value);
    const expirationDate = document.getElementById('editExpirationDate').value;

    const requestData = { itemId, productName, brand, category, quantity, expirationDate };

    fetch('../inventory/update_inventory_item.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            loadGroceryData();
            document.getElementById('editItemForm').style.display = 'none';
            alert('Item updated successfully!');
        } else {
            alert('Error updating item: ' + result.message);
        }
    })
    .catch(error => console.error('Error updating item:', error));
}

// cancel edit function, hide form
function cancelEdit() {
    document.getElementById('editItemForm').style.display = 'none';
}

// Load data when the page loads
document.addEventListener('DOMContentLoaded', loadGroceryData);