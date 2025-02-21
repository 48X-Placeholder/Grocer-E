function loadDashboardData() {
    fetch('fetch_dashboard_data.php')
        .then(response => response.json())
        .then(data => {
            // Populate Shopping List Table
            const shoppingTable = document.getElementById('shoppingDashboardTable');
            shoppingTable.innerHTML = '';

            if (data.shoppingList.length === 0) {
                shoppingTable.innerHTML = `<tr><td colspan="4" style="text-align:center;">No items in shopping list.</td></tr>`;
            } else {
                data.shoppingList.forEach(item => {
                    const row = `
                        <tr>
                            <td>${item.ProductName}</td>
                            <td>${item.Brand}</td>
                            <td>${item.Category}</td>
                            <td>${item.QuantityNeeded}</td>
                        </tr>
                    `;
                    shoppingTable.insertAdjacentHTML('beforeend', row);
                });
            }

            // Populate Inventory List Table
            const inventoryTable = document.getElementById('inventoryDashboardTable');
            inventoryTable.innerHTML = '';

            if (data.inventoryList.length === 0) {
                inventoryTable.innerHTML = `<tr><td colspan="5" style="text-align:center;">No items in inventory.</td></tr>`;
            } else {
                data.inventoryList.forEach(item => {
                    const row = `
                        <tr>
                            <td>${item.ProductName}</td>
                            <td>${item.Brand}</td>
                            <td>${item.Category}</td>
                            <td>${item.Quantity}</td>
                            <td>${item.ExpirationDate || 'N/A'}</td>
                        </tr>
                    `;
                    inventoryTable.insertAdjacentHTML('beforeend', row);
                });
            }
        })
        .catch(error => console.error('Error fetching dashboard data:', error));
}

// Load data when the page loads
document.addEventListener('DOMContentLoaded', loadDashboardData);