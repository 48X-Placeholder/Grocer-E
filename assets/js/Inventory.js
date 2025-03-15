
// Function to load inventory data from the database
function loadGroceryData() {
    fetch('../api/inventory/fetch/', {redirect: 'follow', referrerPolicy: 'no-referrer'})
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('groceryTableBody');
            tableBody.innerHTML = '';

            if (data.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="9" style="text-align: center; font-style: italic;">No items in inventory.</td></tr>`;
                return;
            }

            data.forEach(item => {
                const expirationDisplay = item.ExpirationDate || "N/A";
                let statusMessages = []; // Store multiple alerts
                let alertClass = "";
                let alertIcon = "⚠️"; // Default alert icon
                let expirationHighlightClass = ""; // Default empty

                // Missing UPC / Expiration alerts
                if (!item.ExpirationDate && (!item.UPC || item.UPC === "UNKNOWN")) {
                    statusMessages.push("Missing UPC and Expiration Date");
                } else if (!item.UPC || item.UPC === "UNKNOWN") {
                    statusMessages.push("Missing UPC");
                } else if (!item.ExpirationDate) {
                    statusMessages.push("Missing Expiration Date");
                }

                // Low stock alert
                if (item.Quantity <= 1) {  
                    statusMessages.push("Low Stock");  
                    alertClass = "low-stock";
                }

                // Expiration alerts
                let expirationDate = item.ExpirationDate ? new Date(item.ExpirationDate) : null;
                let today = new Date();

                if (expirationDate) {
                    let sevenDaysAhead = new Date();
                    sevenDaysAhead.setDate(today.getDate() + 7);

                    if (expirationDate < today) {
                        statusMessages.unshift("Expired");
                        alertClass = "expired";
                        expirationHighlightClass = "expired-highlight"; // Mark cell for red highlighting
                        alertIcon = "❗"; // High alert for expired items
                    } else if (expirationDate < sevenDaysAhead) {
                        statusMessages.push("Expiring Soon");
                        alertClass = "expiring-soon";
                        expirationHighlightClass = 'expiring-highlight'; // Mark cell for yellow highlighting
                    }
                }

                // Apply the status column update
                const statusMessage = statusMessages.length > 0 ? statusMessages.join(" | ") : "";
                const statusColumn = statusMessage
                    ? `<td class="alert-column ${alertClass}" title="${statusMessage}">${alertIcon}</td>`
                    : `<td></td>`;

                // Apply the class to the expiration date cell if needed
                const row = `
                    <tr id="row-${item.InventoryItemId}">
                        <td><input type="checkbox" class="item-checkbox" data-id="${item.InventoryItemId}"></td>
                        <td><span class="edit-text">${item.ProductName}</span><input class="edit-input hidden" type="text" value="${item.ProductName}"></td>
                        <td><span class="edit-text">${item.Brand}</span><input class="edit-input hidden" type="text" value="${item.Brand}"></td>
                        <td><span class="edit-text">${item.Category}</span><input class="edit-input hidden" type="text" value="${item.Category}"></td>
                        <td><span class="edit-text">${item.Quantity}</span><input class="edit-input hidden" type="number" value="${item.Quantity}"></td>
                        <td class="expiration-date ${expirationHighlightClass}">
                            <span class="edit-text">${expirationDisplay}</span>
                            <input class="edit-input hidden" type="date" value="${item.ExpirationDate || ''}">
                        </td>

                        <td class="upc-column">
    <span class="edit-text">${item.UPC || ''}</span>
    <input class="edit-input hidden" type="text" value="${item.UPC || ''}" placeholder="Enter UPC">
</td>

                        ${statusColumn}
                        <td>
                            <button class="edit-btn" onclick="toggleEditMode(${item.InventoryItemId})">Edit</button>
                            <button class="save-btn hidden" onclick="saveEdit(${item.InventoryItemId})">Save</button>
                            <button class="cancel-btn hidden" onclick="cancelEdit(${item.InventoryItemId})">Cancel</button>
                        </td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML('beforeend', row);
            });
        })
        .catch(error => console.error('Error fetching data:', error));
}

// Allows table sorting on various columns
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

// Add item function
function addItem() {
    const upc = document.getElementById('upcCode').value.trim();
    const productName = document.getElementById('productName').value.trim();
    const brand = document.getElementById('brand').value.trim();
    const category = document.getElementById('category').value.trim();
    const quantity = parseInt(document.getElementById('quantity').value);
    const expirationDate = document.getElementById('expirationDate').value.trim();
    const formattedExpiration = expirationDate === "" ? null : expirationDate;

    const requestData = { upcCode: upc, productName, brand, category, quantity, expirationDate: formattedExpiration };

    // Check that primary text fields have values
    if (!upc || !productName || !brand || !category) {
        alert("Please fill in all required fields.");
        return;
    }
    
    // Check that quantityNeeded has a positive value
    if (isNaN(quantity) || quantity <= 0) {
        alert("Quantity must be a number greater than 0.");
        return;
    }    

    console.log("Sending request data:", requestData); //for debugging


    fetch('../api/inventory/add/', {
        method: 'POST',
        redirect: 'follow',
        referrerPolicy: 'no-referrer',
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

    fetch('../api/inventory/delete/', {
        method: 'POST',
        redirect: 'follow',
        referrerPolicy: 'no-referrer',
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

// alternative delete function used when editing quantity to 0
function deleteItem(itemId) {
    fetch('../api/inventory/delete/', {
        method: 'POST',
        redirect: 'follow',
        referrerPolicy: 'no-referrer',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ itemId }) // Send as single ID, not an array
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            loadGroceryData(); // Reload table after deletion
        } else {
            alert('Error deleting item: ' + result.message);
        }
    })
    .catch(error => console.error('Error deleting item:', error));
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

// Toggle editing mode
function toggleEditMode(itemId) {
    const row = document.getElementById(`row-${itemId}`);
    if (!row) {
        console.error(`Row with ID row-${itemId} not found`);
        return;
    }

    console.log(`Entering edit mode for row: ${itemId}`);

    // Store original values in dataset in case of cancel
    row.dataset.originalProductName = row.cells[1].innerText.trim();
    row.dataset.originalBrand = row.cells[2].innerText.trim();
    row.dataset.originalCategory = row.cells[3].innerText.trim();
    row.dataset.originalQuantity = row.cells[4].innerText.trim();
    row.dataset.originalExpirationDate = row.cells[5].innerText.trim();
    row.dataset.originalUPC = row.querySelector('.upc-column .edit-text')?.innerText.trim() || '';

    // Replace text with input fields
    row.innerHTML = `
        <td><input type="checkbox" class="item-checkbox" data-id="${itemId}"></td>
        <td><input type="text" class="edit-input" id="edit-product-${itemId}" value="${row.dataset.originalProductName}"></td>
        <td><input type="text" class="edit-input" id="edit-brand-${itemId}" value="${row.dataset.originalBrand}"></td>
        <td>
            <select class="edit-category" id="edit-category-${itemId}">
                <option value="">Select a Category</option>
                <option value="Fruits">Fruits</option>
                <option value="Vegetables">Vegetables</option>
                <option value="Dairy">Dairy</option>
                <option value="Bakery">Bakery</option>
                <option value="Meat">Meat</option>
                <option value="Seafood">Seafood</option>
                <option value="Beverages">Beverages</option>
                <option value="Snacks">Snacks</option>
                <option value="Frozen Foods">Frozen Foods</option>
                <option value="Pantry">Pantry</option>
                <option value="Canned Goods">Canned Goods</option>
                <option value="Condiments">Condiments</option>
                <option value="Household Items">Household Items</option>
                <option value="Personal Care">Personal Care</option>
            </select>
        </td>
        <td><input type="number" class="edit-input" id="edit-quantity-${itemId}" value="${row.dataset.originalQuantity}"></td>
        <td><input type="date" class="edit-input" id="edit-expiration-${itemId}" value="${row.dataset.originalExpirationDate}"></td>
        <td class="upc-column"><input type="text" class="edit-input" id="edit-upc-${itemId}" value="${row.dataset.originalUPC}" placeholder="Enter UPC"></td>
        <td></td>
        <td>
            <button class="save-btn" onclick="saveEdit(${itemId})">Save</button>
            <button class="cancel-btn" onclick="cancelEdit(${itemId})">Cancel</button>
        </td>
    `;

    // Initialize Select2 after recreating the dropdown
    $(`#edit-category-${itemId}`).val(row.dataset.originalCategory).select2({
        placeholder: "Select a Category",
        width: '100%',
        dropdownAutoWidth: true,
        minimumResultsForSearch: 0
    });

    // Activate edit mode class
    row.classList.add('edit-mode');
    document.querySelector("table").classList.add("edit-mode-active");

    console.log("Select element found:", $(`#edit-category-${itemId}`).length); // Debugging
}



// Save edits
function saveEdit(itemId) {
    const row = document.getElementById(`row-${itemId}`);

    const upcInput = row.querySelector('.upc-column input').value.trim() || null;

    const quantityInput = row.querySelector(`#edit-quantity-${itemId}`);
    const quantity = parseInt(quantityInput.value);

    if (isNaN(quantity) || quantity < 0) {
        alert("Quantity must be a number greater than or equal to 0.");
        return;
    }

    if (quantity === 0 && !confirm("This item is now out of stock. Would you like to remove it from inventory?")) {
        return;
    } else if (quantity === 0) {
        deleteItem(itemId);
        return;
    }

    const requestData = {
        itemId,
        productName: row.querySelector(`#edit-product-${itemId}`).value,
        brand: row.querySelector(`#edit-brand-${itemId}`).value,
        category: row.querySelector(`#edit-category-${itemId}`).value,
        quantity,
        expirationDate: row.querySelector(`#edit-expiration-${itemId}`).value || null,
        upc: upcInput
    };

    fetch('../api/inventory/update/', {
        method: 'POST',
        redirect: 'follow',
        referrerPolicy: 'no-referrer',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            loadGroceryData(); // Reload the updated data
        } else {
            alert('Error updating item: ' + result.message);
        }
    })
    .catch(error => console.error('Error updating item:', error));
}

// Updates the alert column and expiration date styling for a specific row
function updateRowAlerts(row, expirationDate) {
    let today = new Date();
    let sevenDaysAhead = new Date();
    sevenDaysAhead.setDate(today.getDate() + 7);

    let expirationCell = row.querySelector('td:nth-child(6)'); // Expiration date column
    let alertCell = row.querySelector('td:nth-child(8)'); // Alerts column

    // Remove existing highlight classes
    expirationCell.classList.remove("expired-highlight", "expiring-highlight");

    let statusMessages = [];
    let alertIcon = "⚠️"; // Default alert icon
    let alertClass = "";

    if (!expirationDate) {
        statusMessages.push("Missing Expiration Date");
    } else {
        let expDate = new Date(expirationDate);

        if (expDate < today) {
            statusMessages.push("Expired");
            expirationCell.classList.add("expired-highlight");
            alertClass = "expired";
            alertIcon = "❗"; // High alert for expired items
        } else if (expDate < sevenDaysAhead) {
            statusMessages.push("Expiring Soon");
            expirationCell.classList.add("expiring-highlight");
            alertClass = "expiring-soon";
        }
    }

    // Update the alerts column with the new status
    alertCell.className = alertClass;
    alertCell.title = statusMessages.join(" | ");
    alertCell.innerHTML = statusMessages.length > 0 ? alertIcon : "";
}

// Cancel edit function, hide form
function cancelEdit(itemId) {
    const row = document.getElementById(`row-${itemId}`);
    if (!row) return;

    loadGroceryData(); // Simply reload data to restore original values
}

// Load data when the page loads
document.addEventListener('DOMContentLoaded', loadGroceryData);