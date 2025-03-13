// Function to load inventory data from the database
function loadGroceryData() {
    fetch('../api/inventory/fetch/', { redirect: 'follow', referrerPolicy: 'no-referrer' })
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('groceryTableBody');
            tableBody.innerHTML = ''; // Clear existing table content

            if (data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="9" style="text-align: center; font-style: italic; color: #888;">
                            Your inventory is empty. Add some items to get started!
                        </td>
                    </tr>
                `;
                return;
            }

            data.forEach(item => {
                const expirationDisplay = item.ExpirationDate || "N/A";
                let expirationHighlightClass = "";

                let expirationDate = item.ExpirationDate ? new Date(item.ExpirationDate) : null;
                let today = new Date();

                if (expirationDate) {
                    let sevenDaysAhead = new Date();
                    sevenDaysAhead.setDate(today.getDate() + 7);

                    if (expirationDate < today) {
                        expirationHighlightClass = "expired-highlight";
                    } else if (expirationDate < sevenDaysAhead) {
                        expirationHighlightClass = 'expiring-highlight';
                    }
                }

                // The category dropdown should be hidden initially and only appear in edit mode
                const row = `
                    <tr id="row-${item.InventoryItemId}">
                        <td><input type="checkbox" class="item-checkbox" data-id="${item.InventoryItemId}"></td>
                        <td>
                            <span class="edit-text">${item.ProductName}</span>
                            <input class="edit-input name hidden" type="text" value="${item.ProductName}">
                        </td>
                        <td>
                            <span class="edit-text">${item.Brand}</span>
                            <input class="edit-input brand hidden" type="text" value="${item.Brand}">
                        </td>
                        <td>
                            <span class="edit-text">${item.Category}</span>
                            <select class="edit-input category hidden">
                                <option value="">Select a Category</option>
                                <option value="Fresh Produce" ${item.Category === "Fresh Produce" ? "selected" : ""}>Fresh Produce</option>
                                <option value="Dairy & Eggs" ${item.Category === "Dairy & Eggs" ? "selected" : ""}>Dairy & Eggs</option>
                                <option value="Meat & Seafood" ${item.Category === "Meat & Seafood" ? "selected" : ""}>Meat & Seafood</option>
                                <option value="Deli & Prepared Foods" ${item.Category === "Deli & Prepared Foods" ? "selected" : ""}>Deli & Prepared Foods</option>
                                <option value="Bakery" ${item.Category === "Bakery" ? "selected" : ""}>Bakery</option>
                                <option value="Frozen Foods" ${item.Category === "Frozen Foods" ? "selected" : ""}>Frozen Foods</option>
                                <option value="Pantry Staples (Dry Goods)" ${item.Category === "Pantry Staples (Dry Goods)" ? "selected" : ""}>Pantry Staples (Dry Goods)</option>
                                <option value="Snacks & Sweets" ${item.Category === "Snacks & Sweets" ? "selected" : ""}>Snacks & Sweets</option>
                                <option value="Beverages" ${item.Category === "Beverages" ? "selected" : ""}>Beverages</option>
                                <option value="Cereal & Breakfast Foods" ${item.Category === "Cereal & Breakfast Foods" ? "selected" : ""}>Cereal & Breakfast Foods</option>
                                <option value="International Foods" ${item.Category === "International Foods" ? "selected" : ""}>International Foods</option>
                                <option value="Organic & Health Foods" ${item.Category === "Organic & Health Foods" ? "selected" : ""}>Organic & Health Foods</option>
                                <option value="Baby & Toddler Food" ${item.Category === "Baby & Toddler Food" ? "selected" : ""}>Baby & Toddler Food</option>
                                <option value="Pet Food" ${item.Category === "Pet Food" ? "selected" : ""}>Pet Food</option>
                            </select>
                        </td>
                        <td>
                            <span class="edit-text">${item.Quantity}</span>
                            <input class="edit-input quantity hidden" type="number" value="${item.Quantity}">
                        </td>
                        <td class="expiration-date ${expirationHighlightClass}">
                            <span class="edit-text">${expirationDisplay}</span>
                            <input class="edit-input expiration hidden" type="date" value="${item.ExpirationDate || ''}">
                        </td>
                        <td class="upc-column hidden">
                            <input class="edit-input" type="text" value="${item.UPC || ''}" placeholder="Enter UPC">
                        </td>
                        <td>
                            <button class="edit-btn" onclick="toggleEditMode(${item.InventoryItemId})">Edit</button>
                            <button class="save-btn hidden" onclick="saveEdit(${item.InventoryItemId})">Save</button>
                            <button class="cancel-btn hidden" onclick="cancelEdit(${item.InventoryItemId})">Cancel</button>
                        </td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML('beforeend', row);
            });

            // Ensure Select2 is only applied when in edit mode
            $('.edit-input.category').each(function() {
                $(this).select2({
                    placeholder: "Select a Category",
                    width: 'resolve',
                    dropdownParent: $('body')
                }).data('select2').$container.hide();
            });
        })
        .catch(error => console.error('Error fetching inventory:', error));
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
    const upcCode = document.getElementById('upcCode').value.trim();
    const productName = document.getElementById('productName').value.trim();
    const brand = document.getElementById('brand').value.trim();
    const category = document.getElementById('category').value.trim();
    const quantity = parseInt(document.getElementById('quantity').value);
    const expirationDate = document.getElementById('expirationDate').value.trim();
    const formattedExpiration = expirationDate === "" ? null : expirationDate;

    const requestData = { upcCode, productName, brand, category, quantity, expirationDate: formattedExpiration };

    // Check that primary text fields have values
    if (!upcCode || !productName || !brand || !category) {
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
        console.error(`Row with ID ${itemId} not found.`);
        return;
    }

    console.log(`Entering edit mode for row: ${itemId}`);

    row.dataset.originalProductName = row.querySelector('.edit-input.name').value;
    row.dataset.originalBrand = row.querySelector('.edit-input.brand').value;
    row.dataset.originalCategory = row.querySelector('.edit-input.category').value;
    row.dataset.originalQuantity = row.querySelector('.edit-input.quantity').value;
    row.dataset.originalExpirationDate = row.querySelector('.edit-input.expiration').value;
    row.dataset.originalUPC = row.querySelector('.upc-column input').value || ''; 

    row.classList.add('edit-mode');
    document.querySelector("table").classList.add("edit-mode-active");

    row.querySelectorAll('.edit-text').forEach(el => el.classList.add('hidden'));
    row.querySelectorAll('.edit-input').forEach(el => el.classList.remove('hidden'));

    // Properly show the category dropdown
    const categoryDropdown = $(row).find('.edit-input.category');
    categoryDropdown.select2({
        placeholder: "Select a Category",
        width: 'resolve'
    }).data('select2').$container.show();

    row.querySelector('.edit-btn').classList.add('hidden');
    row.querySelector('.save-btn').classList.remove('hidden');
    row.querySelector('.cancel-btn').classList.remove('hidden');
}


// Save edits
function saveEdit(itemId) {
    const row = document.getElementById(`row-${itemId}`);
    const upcInput = row.querySelector('.upc-column input').value.trim() || null;

    const quantityInput = row.querySelector('input[type="number"]');
    const quantity = parseInt(quantityInput.value);

    // Validation: Prevent negative numbers
    if (isNaN(quantity) || quantity < 0) {
        alert("Quantity must be a number greater than or equal to 0.");
        return;
    }

    // Confirmation for zero quantity
    if (quantity === 0) {
        const confirmDelete = confirm("This item is now out of stock. Would you like to remove it from inventory?");
        
        if (confirmDelete) {
            deleteItem(itemId); // Call delete function
            return;
        }
    }

    const requestData = {
        itemId,
        productName: row.querySelectorAll('input[type="text"]')[0].value,
        brand: row.querySelectorAll('input[type="text"]')[1].value,
        category: row.querySelectorAll('input[type="text"]')[2].value,
        quantity: row.querySelector('input[type="number"]').value,
        expirationDate: row.querySelector('input[type="date"]').value || null,
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
            // Update the row dynamically
            row.querySelectorAll('.edit-text')[0].innerText = requestData.productName;
            row.querySelectorAll('.edit-text')[1].innerText = requestData.brand;
            row.querySelectorAll('.edit-text')[2].innerText = requestData.category;
            row.querySelectorAll('.edit-text')[3].innerText = requestData.quantity;
            row.querySelectorAll('.edit-text')[4].innerText = requestData.expirationDate || "N/A";

            // Exit edit mode for this row
            row.classList.remove('edit-mode');
            row.querySelectorAll('.edit-text').forEach(el => el.classList.remove('hidden'));
            row.querySelectorAll('.edit-input').forEach(el => el.classList.add('hidden'));
            row.querySelector('.upc-column input').style.display = 'none';

            row.querySelector('.edit-btn').classList.remove('hidden');
            row.querySelector('.save-btn').classList.add('hidden');
            row.querySelector('.cancel-btn').classList.add('hidden');

            // **Recalculate Alerts for This Row Only**
            updateRowAlerts(row, requestData.expirationDate);

            // Check if any rows are still being edited
            const anyRowsStillEditing = document.querySelectorAll('.edit-mode').length > 0;
            if (!anyRowsStillEditing) {
                document.querySelector("table").classList.remove("edit-mode-active");
                loadGroceryData(); // Reload the table when the last edit is saved
            }
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

function cancelEdit(itemId) {
    const row = document.getElementById(`row-${itemId}`);
    if (!row) {
        console.error(`Row with ID ${itemId} not found.`);
        return;
    }

    console.log(`Canceling edit for row: ${itemId}`);

    row.querySelectorAll('.edit-text').forEach(el => el.classList.remove('hidden'));
    row.querySelectorAll('.edit-input').forEach(el => el.classList.add('hidden'));

    // Properly hide the category dropdown
    $(row).find('.edit-input.category').each(function() {
        $(this).data('select2').$container.hide();
    });

    row.classList.remove('edit-mode');
    row.querySelector('.edit-btn').classList.remove('hidden');
    row.querySelector('.save-btn').classList.add('hidden');
    row.querySelector('.cancel-btn').classList.add('hidden');

    if (!document.querySelectorAll('.edit-mode').length) {
        document.querySelector("table").classList.remove("edit-mode-active");
    }
}

// Load data when the page loads
document.addEventListener('DOMContentLoaded', loadGroceryData);



