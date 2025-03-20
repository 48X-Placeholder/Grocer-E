// Define predefined category list
const predefinedCategories = [
    "Dairy", "Meat", "Vegetables", "Fruits", "Beverages", "Bakery",
    "Frozen Foods", "Snacks", "Canned Goods", "Grains", "Condiments",
    "Deli", "Seafood", "Spices & Herbs", "Pasta & Rice", "Household Items",
    "Personal Care"
];

// Function to load shopping list data from PHP
function loadShoppingList() {
    fetch('../api/shopping-list/fetch/', {redirect: 'follow', referrerPolicy: 'no-referrer'})
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('shoppingTableBody');
            tableBody.innerHTML = ''; // Clear existing table content

            if (data.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align: center; font-style: italic; color: #888;">
                            Your shopping list is empty. Add some items to get started!
                        </td>
                    </tr>
                `;
                return;
            }

            data.forEach(item => {
                console.log("Received Category:", item.Category);
                // Match category for scanned items
                const category = item.Category;
            
                const row = `
                    <tr id="row-${item.ListItemId}">
                        <td><input type="checkbox" class="item-checkbox" data-id="${item.ListItemId}"></td>
                        <td><span class="edit-text">${item.ProductName}</span>
                            <input class="edit-input name hidden" type="text" value="${item.ProductName}"></td>
                        <td><span class="edit-text">${item.Brand}</span>
                            <input class="edit-input brand hidden" type="text" value="${item.Brand}"></td>
                        <td>
                            <span class="edit-text">${category}</span>
                            <select class="edit-input category hidden">
                                ${predefinedCategories.map(cat => 
                                    `<option value="${cat}" ${cat === category ? 'selected' : ''}>${cat}</option>`
                                ).join('')}
                            </select>
                        </td>
                        <td><span class="edit-text">${item.QuantityNeeded}</span>
                            <input class="edit-input quantity hidden" type="number" value="${item.QuantityNeeded}"></td>
                        <td>
                            <button class="edit-btn" onclick="toggleEditMode(${item.ListItemId})">Edit</button>
                            <button class="save-btn hidden" onclick="saveEdit(${item.ListItemId})">Save</button>
                            <button class="cancel-btn hidden" onclick="cancelEdit(${item.ListItemId})">Cancel</button>
                        </td>
                    </tr>
                `;
                document.getElementById('shoppingTableBody').insertAdjacentHTML('beforeend', row);
            });            

            // Reinitialize Select2 for dynamically added category dropdowns
            $('.category-dropdown').select2({
                placeholder: "Select a Category",
                allowClear: true,
                width: '100%'
            });


        })
        .catch(error => console.error('Error fetching shopping list:', error));
}

// Function to sort the table
function sortTable(columnIndex, dataType, headerElement) {
    const tableBody = document.getElementById("shoppingTableBody");
    const rows = Array.from(tableBody.rows);
    const adjustedColumnIndex = columnIndex + 1;  

    const currentOrder = headerElement.getAttribute("data-order");
    const newOrder = currentOrder === "asc" ? "desc" : "asc";
    headerElement.setAttribute("data-order", newOrder);

    document.querySelectorAll("th").forEach(th => th.classList.remove("asc", "desc"));
    headerElement.classList.add(newOrder);

    const sortedRows = rows.sort((a, b) => {
        let cellA = a.cells[adjustedColumnIndex].innerText.trim();
        let cellB = b.cells[adjustedColumnIndex].innerText.trim();

        if (dataType === "number") {
            return newOrder === "asc" ? cellA - cellB : cellB - cellA;
        } else {
            return newOrder === "asc" ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
        }
    });

    tableBody.innerHTML = "";
    sortedRows.forEach(row => tableBody.appendChild(row));
}

// Handles deleting selected items
function deleteSelectedItems() {
    const selectedIds = Array.from(document.querySelectorAll('.item-checkbox:checked'))
        .map(checkbox => checkbox.getAttribute('data-id'));

    if (selectedIds.length === 0) {
        alert('Please select at least one item to delete.');
        return;
    }

    fetch('../api/shopping-list/delete/', {
        method: 'POST',
        redirect: 'follow',
        referrerPolicy: 'no-referrer',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ itemIds: selectedIds })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            loadShoppingList();
            alert('Selected items deleted successfully!');
        } else {
            alert('Error deleting items: ' + result.message);
        }
    })
    .catch(error => console.error('Error deleting items:', error));
}

// Handles exporting selected shopping items to inventory
function exportSelectedItems() {
    const selectedIds = Array.from(document.querySelectorAll('.item-checkbox:checked'))
        .map(checkbox => checkbox.getAttribute('data-id'));

    if (selectedIds.length === 0) {
        alert('Please select at least one item to export.');
        return;
    }

    fetch('../api/shopping-list/export/', {
        method: 'POST',
        redirect: 'follow',
        referrerPolicy: 'no-referrer',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ itemIds: selectedIds })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Selected items moved to inventory!');
            loadShoppingList();
        } else {
            alert('Error exporting items: ' + result.message);
        }
    })
    .catch(error => console.error('Error exporting items:', error));
}

// Handles adding an item to the shopping list
function addShopItem() {
    const productName = document.getElementById('productName').value.trim();
    const brand = document.getElementById('brand').value.trim();
    const category = document.getElementById('category').value.trim();
    const quantityNeeded = parseInt(document.getElementById('quantityNeeded').value);

    if (!productName || !brand || !category) {
        alert("Please fill in all required fields.");
        return;
    }

    if (isNaN(quantityNeeded) || quantityNeeded <= 0) {
        alert("Quantity must be a number greater than 0.");
        return;
    }

    fetch('../api/shopping-list/add/', {
        method: 'POST',
        redirect: 'follow',
        referrerPolicy: 'no-referrer',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ productName, brand, category, quantityNeeded })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            loadShoppingList(); // **Refresh list after updating quantity or adding a new row**
            cancelAddItem();
            alert(result.message);
        } else {
            alert("Error adding item: " + result.message);
        }
    })
    .catch(error => console.error('Error adding item:', error));
}
// Show/hide the add item form
function toggleAddItemForm() {
    const form = document.getElementById('addItemForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

// Clear and hide the add item form
function cancelAddItem() {
    document.getElementById('addItemForm').style.display = 'none';
    document.getElementById('productName').value = '';
    document.getElementById('brand').value = '';
    document.getElementById('category').value = '';
    document.getElementById('quantityNeeded').value = '';
}

// Function to toggle inline editing for a row
function toggleEditMode(itemId) {
    const row = document.getElementById(`row-${itemId}`);

    if (!row) {
        console.error(`Row with ID ${itemId} not found.`);
        return;
    }

    // Store original values as data attributes
    row.dataset.originalProductName = row.querySelector('.edit-input.name').value;
    row.dataset.originalBrand = row.querySelector('.edit-input.brand').value;
    row.dataset.originalCategory = row.querySelectorAll('.edit-text')[2].innerText;
    row.dataset.originalQuantity = row.querySelector('.edit-input.quantity').value;


    // Show inputs and hide text fields
    row.querySelectorAll('.edit-text').forEach(el => el.classList.add('hidden'));
    row.querySelectorAll('.edit-input').forEach(el => el.classList.remove('hidden'));

     // Convert the category field into a Select2 dropdown
     const categoryDropdown = $(row).find('.edit-input.category');
     categoryDropdown.select2({
         placeholder: "Select a Category",
         allowClear: true,
         width: '100%'
     });

     // Set the dropdown value to match the existing category
    categoryDropdown.val(row.dataset.originalCategory).trigger('change');

    // Toggle button visibility
    row.querySelector('.edit-btn').classList.add('hidden');
    row.querySelector('.save-btn').classList.remove('hidden');
    row.querySelector('.cancel-btn').classList.remove('hidden');

    // Mark row as being edited
    row.classList.add('edit-mode');
}

// Function to save edited shopping list item
function saveEdit(itemId) {
    const row = document.getElementById(`row-${itemId}`);

    if (!row) {
        console.error(`Row with ID ${itemId} not found.`);
        return;
    }

    // Targeting the correct input fields based on your table structure
    const productNameInput = row.querySelector('.edit-input.name');
    const brandInput = row.querySelector('.edit-input.brand');
    const categoryDropDown = $(row).find('.edit-input.category'); // Use jQuery to get Select2 value
    const categoryValue = categoryDropDown.val();
    const quantityInput = row.querySelector('.edit-input.quantity');

    // Ensure extracted values are not null
    const requestData = {
        itemId,
        productName: productNameInput ? productNameInput.value.trim() : '',
        brand: brandInput ? brandInput.value.trim() : '',
        category: categoryValue,
        quantityNeeded: quantityInput ? quantityInput.value.trim() : ''
    };

    if (!requestData.productName || !requestData.brand || !requestData.category) {
        alert("Please fill in all fields.");
        return;
    }

    if (isNaN(requestData.quantityNeeded) || requestData.quantityNeeded <= 0) {
        alert("Error: Quantity must be greater than 0.");
        return;
    }

    console.log("Saving Item with Data:", requestData); // Debugging

    fetch('../api/shopping-list/update/', {
        method: 'POST',
        redirect: 'follow',
        referrerPolicy: 'no-referrer',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            console.log("Item successfully updated:", requestData);

            // Update only the edited row dynamically
            row.querySelector('.edit-text').innerText = requestData.productName;
            row.querySelectorAll('.edit-text')[1].innerText = requestData.brand;
            row.querySelectorAll('.edit-text')[2].innerText = requestData.category;
            row.querySelectorAll('.edit-text')[3].innerText = requestData.quantityNeeded;

            // ✅ Remove Select2 and hide the dropdown
            categoryDropDown.select2('destroy');  // Destroy Select2 instance
            categoryDropDown.addClass('hidden');  // Hide dropdown
            // row.querySelector('.edit-text.category').classList.remove('hidden'); // Show text

            // Restore normal row display
            row.classList.remove('edit-mode');
            row.querySelectorAll('.edit-text').forEach(el => el.classList.remove('hidden'));
            row.querySelectorAll('.edit-input').forEach(el => el.classList.add('hidden'));

            row.querySelector('.edit-btn').classList.remove('hidden');
            row.querySelector('.save-btn').classList.add('hidden');
            row.querySelector('.cancel-btn').classList.add('hidden');

        } else {
            alert('Error updating item: ' + result.message);
        }
    })
    .catch(error => console.error('Error updating item:', error));
}

// Function to cancel inline editing and revert to original display
function cancelEdit(itemId) {
    const row = document.getElementById(`row-${itemId}`);

    if (!row) {
        console.error(`Row with ID ${itemId} not found.`);
        return;
    }

    // Restore original values from stored data attributes
    row.querySelector('.edit-input.name').value = row.dataset.originalProductName;
    row.querySelector('.edit-input.brand').value = row.dataset.originalBrand;
    
    // Ensure category dropdown reverts to original text
    $(row).find('.edit-input.category').val(row.dataset.originalCategory).trigger('change');
    
    // Hide all input fields again
    row.querySelectorAll('.edit-text').forEach(el => el.classList.remove('hidden'));
    row.querySelectorAll('.edit-input').forEach(el => el.classList.add('hidden'));

    // Destroy Select2 dropdown instance to prevent persisting
    $(row).find('.edit-input.category').select2("destroy");

    // Toggle button visibility
    row.querySelector('.edit-btn').classList.remove('hidden');
    row.querySelector('.save-btn').classList.add('hidden');
    row.querySelector('.cancel-btn').classList.add('hidden');

    row.classList.remove('edit-mode');
}


document.addEventListener('DOMContentLoaded', loadShoppingList);
