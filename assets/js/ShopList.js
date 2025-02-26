// Function to load shopping list data from PHP
function loadShoppingList() {
    fetch('../shopping-list/fetch_shopping_list.php')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('shoppingTableBody');
            tableBody.innerHTML = ''; // Clear existing table content

            if (data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; font-style: italic; color: #888;">
                            Your shopping list is empty. Add some items to get started!
                        </td>
                    </tr>
                `;
                return;
            }

            data.forEach(item => {
                const checked = item.Purchased ? 'checked' : '';
            
                const row = `
                    <tr>
                        <td><input type="checkbox" class="item-checkbox" data-id="${item.ListItemId}"></td>
                        <td>${item.ProductName}</td>
                        <td>${item.Brand}</td>
                        <td>${item.Category}</td>
                        <td>${item.QuantityNeeded}</td>
                        <td><input type="checkbox" class="purchased-checkbox" data-id="${item.ListItemId}" ${checked} onchange="togglePurchased(${item.ListItemId}, this.checked)"></td>
                        <td><button class="edit-btn" onclick="editShopItem(${item.ListItemId}, '${item.ProductName}', '${item.Brand}', '${item.Category}', ${item.QuantityNeeded})">Edit</button></td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML('beforeend', row);
            });            
        })
        .catch(error => console.error('Error fetching shopping list:', error));
}

// Function to sort the table (reuses inventory sorting logic)
function sortTable(columnIndex, dataType, headerElement) {
    const tableBody = document.getElementById("shoppingTableBody");
    const rows = Array.from(tableBody.rows);

    // Adjust column index to account for the "Select" column
    const adjustedColumnIndex = columnIndex + 1;

    // Determine current sort order and toggle it
    const currentOrder = headerElement.getAttribute("data-order");
    const newOrder = currentOrder === "asc" ? "desc" : "asc";
    headerElement.setAttribute("data-order", newOrder);

    // Remove sorting indicators from all headers
    document.querySelectorAll("th").forEach(th => th.classList.remove("asc", "desc"));
    headerElement.classList.add(newOrder);

    // Sort rows based on data type
    const sortedRows = rows.sort((a, b) => {
        let cellA = a.cells[adjustedColumnIndex].innerText.trim();
        let cellB = b.cells[adjustedColumnIndex].innerText.trim();

        if (dataType === "number") {
            const numA = parseFloat(cellA) || 0;
            const numB = parseFloat(cellB) || 0;
            return newOrder === "asc" ? numA - numB : numB - numA;
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

// Function to toggle purchase status in db
function togglePurchased(itemId, isChecked) {
    fetch('../shopping-list/update_purchased_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ itemId, purchased: isChecked })
    })
    .then(response => response.json())
    .then(result => {
        if (!result.success) {
            alert('Error updating item: ' + result.message);
        }
    })
    .catch(error => console.error('Error updating purchased status:', error));
}

// Handles delete item(s) functionality
function deleteSelectedItems() {
    const selectedIds = Array.from(document.querySelectorAll('.item-checkbox:checked'))
        .map(checkbox => checkbox.getAttribute('data-id'));

    if (selectedIds.length === 0) {
        alert('Please select at least one item to delete.');
        return;
    }

    fetch('../shopping-list/delete_shopping_item.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ itemIds: selectedIds })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            loadShoppingList(); // Reload list after deletion
            alert('Selected items deleted successfully!');
        } else {
            alert('Error deleting items: ' + result.message);
        }
    })
    .catch(error => console.error('Error deleting items:', error));
}

// Handles add item functionality
function addShopItem() {
    const productName = document.getElementById('productName').value.trim();
    const brand = document.getElementById('brand').value.trim();
    const category = document.getElementById('category').value.trim();
    const quantityNeeded = parseInt(document.getElementById('quantityNeeded').value);

    if (!productName || !brand || !category || !quantityNeeded) {
        alert("Please fill in all fields.");
        return;
    }

    fetch('../shopping-list/add_shopping_item.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ productName, brand, category, quantityNeeded })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            loadShoppingList(); // Refresh list
            cancelAddItem(); // Hide form
            alert("Item added successfully!");
        } else {
            alert("Error adding item: " + result.message);
        }
    })
    .catch(error => console.error('Error adding item:', error));
}

// Display add item form on click of the add item button
function toggleAddItemForm() {
    const form = document.getElementById('addItemForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

// Clear and hide the add item form
function cancelAddItem() {
    document.getElementById('addItemForm').style.display = 'none';

    // Reset all form fields
    document.getElementById('productName').value = '';
    document.getElementById('brand').value = '';
    document.getElementById('category').value = '';
    document.getElementById('quantityNeeded').value = '';
}

// Triggered when user clicks an Edit button
function editShopItem(id, name, brand, category, quantity) {
    document.getElementById('editShopItemId').value = id;
    document.getElementById('editShopProductName').value = name;
    document.getElementById('editShopBrand').value = brand;
    document.getElementById('editShopCategory').value = category;
    document.getElementById('editShopQuantity').value = quantity;

    document.getElementById('editShopItemForm').style.display = 'block';
}

// Sends data to update_shopping_item.php
function updateShopItem() {
    const itemId = document.getElementById('editShopItemId').value;
    const productName = document.getElementById('editShopProductName').value.trim();
    const brand = document.getElementById('editShopBrand').value.trim();
    const category = document.getElementById('editShopCategory').value.trim();
    const quantity = parseInt(document.getElementById('editShopQuantity').value);

    const requestData = { itemId, productName, brand, category, quantity };

    fetch('../shopping-list/update_shopping_item.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            loadShoppingList(); // Refresh shopping list
            document.getElementById('editShopItemForm').style.display = 'none';
            alert('Item updated successfully!');
        } else {
            alert('Error updating item: ' + result.message);
        }
    })
    .catch(error => console.error('Error updating item:', error));
}

// Hides the display of the edit form
function cancelShopEdit() {
    document.getElementById('editShopItemForm').style.display = 'none';
}

// Load data when the page loads
document.addEventListener('DOMContentLoaded', loadShoppingList);