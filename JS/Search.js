        function handleSearch() {
            const searchQuery = document.getElementById('search').value;
            if (searchQuery) {
                alert(`Searching for: ${searchQuery}`);
            } else {
                alert("Please enter a search query.");
            }
        }