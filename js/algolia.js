document.addEventListener("DOMContentLoaded", function () {
    const appDiv = document.getElementById("algolia-search-app");
    appDiv.innerHTML = `
        <h2>Search Interface</h2>
        <label for="indices">Select Index:</label>
        <select id="indices"></select>
        <br><br>
        <label for="search-query">Search Query:</label>
        <input type="text" id="search-query" placeholder="Enter your query">
        <button id="search-button">Search</button>
        <div id="search-results"></div>
    `;

    const indicesSelect = document.getElementById("indices");
    const searchButton = document.getElementById("search-button");
    const searchQueryInput = document.getElementById("search-query");
    const searchResultsDiv = document.getElementById("search-results");

    // Replace with your Algolia API key and Application ID
    const ALGOLIA_APP_ID = "APP_ID"; // Add your Algolia App ID
    const ALGOLIA_API_KEY = "API_KEY"; // Add your Algolia API Key

    async function fetchIndices() {
        try {
            const response = await fetch(`https://${ALGOLIA_APP_ID}-dsn.algolia.net/1/indexes/`, {
                headers: {
                    "X-Algolia-API-Key": ALGOLIA_API_KEY,
                    "X-Algolia-Application-Id": ALGOLIA_APP_ID
                }
            });
            const data = await response.json();
            if (data.items) {
                data.items.forEach((index) => {
                    const option = document.createElement("option");
                    option.value = index.name;
                    option.textContent = index.name;
                    indicesSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error("Error fetching indices:", error);
        }
    }

    async function performSearch() {
        const indexName = indicesSelect.value;
        const query = searchQueryInput.value;

        if (!indexName || !query) {
            searchResultsDiv.innerHTML = "<p>Please select an index and enter a query.</p>";
            return;
        }

        try {
            const response = await fetch(`https://${ALGOLIA_APP_ID}-dsn.algolia.net/1/indexes/${indexName}/query`, {
                method: "POST",
                headers: {
                    "X-Algolia-API-Key": ALGOLIA_API_KEY,
                    "X-Algolia-Application-Id": ALGOLIA_APP_ID,
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ query })
            });
            const data = await response.json();

            if (data.hits.length > 0) {
                // Generate a table for the relevant fields
                const table = document.createElement("table");
                table.classList.add("results-table");

                // Add table headers
                const thead = document.createElement("thead");
                const headerRow = document.createElement("tr");
                ["Post Title", "Post Excerpt", "Taxonomies", "Post ID", "Post Type"].forEach((header) => {
                    const th = document.createElement("th");
                    th.textContent = header;
                    headerRow.appendChild(th);
                });
                thead.appendChild(headerRow);
                table.appendChild(thead);

                // Add table rows for each hit
                const tbody = document.createElement("tbody");
                data.hits.forEach((hit) => {
                    const row = document.createElement("tr");

                    // Post Title (highlight matching words)
                    const titleCell = document.createElement("td");
                    titleCell.innerHTML = hit.highlightResult?.post_title?.value
                        ? hit.highlightResult.post_title.value
                        : hit.post_title || "N/A"; // Fallback to normal title if highlight is unavailable
                    row.appendChild(titleCell);

                    // Post Excerpt (highlight matching words)
                    const excerptCell = document.createElement("td");
                    excerptCell.innerHTML = hit.highlightResult?.post_excerpt?.value
                        ? hit.highlightResult.post_excerpt.value
                        : hit.post_excerpt || "N/A"; // Fallback to normal excerpt if highlight is unavailable
                    row.appendChild(excerptCell);

                    // Taxonomies (process object to readable format)
                    const taxonomiesCell = document.createElement("td");
                    if (hit.taxonomies && typeof hit.taxonomies === "object") {
                        taxonomiesCell.textContent = JSON.stringify(hit.taxonomies, null, 2)
                            .replaceAll("{", "")
                            .replaceAll("}", "")
                            .replaceAll(",", ", "); // Convert object to easier-to-read string
                    } else {
                        taxonomiesCell.textContent = "N/A";
                    }
                    row.appendChild(taxonomiesCell);

                    // Post ID
                    const postIdCell = document.createElement("td");
                    postIdCell.textContent = hit.post_id ? hit.post_id : "N/A";
                    row.appendChild(postIdCell);

                    // Post Type
                    const postTypeCell = document.createElement("td");
                    postTypeCell.textContent = hit.post_type ? hit.post_type : "N/A";
                    row.appendChild(postTypeCell);

                    tbody.appendChild(row);
                });
                table.appendChild(tbody);

                // Append the table to the search results div
                searchResultsDiv.innerHTML = "<h3>Search Results:</h3>";
                searchResultsDiv.appendChild(table);
            } else {
                searchResultsDiv.innerHTML = "<p>No results found.</p>";
            }
        } catch (error) {
            console.error("Error performing search:", error);
            searchResultsDiv.innerHTML = "<p>An error occurred during the search.</p>";
        }
    }

    fetchIndices();
    searchButton.addEventListener("click", performSearch);
});
