<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_TITLE; ?></title>
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>"
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px 0;
        }

        .header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .header p {
            color: #6c757d;
            font-size: 1.1em;
        }

        .search-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .search-input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .search-input {
            flex: 1;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
        }

        .search-input:focus {
            border-color: #007bff;
        }

        .search-button {
            padding: 15px 25px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-button:hover {
            background: #0056b3;
        }

        .search-button:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }

        .loading {
            display: none;
            text-align: center;
            color: #6c757d;
            margin: 20px 0;
        }

        .results {
            display: none;
        }

        .results-header {
            background: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            border-bottom: 1px solid #e9ecef;
        }

        .results-container {
            background: white;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .result-item {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.3s;
        }

        .result-item:last-child {
            border-bottom: none;
        }

        .result-item:hover {
            background-color: #f8f9fa;
        }

        .result-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .result-title a {
            color: #1a73e8;
            text-decoration: none;
        }

        .result-title a:hover {
            text-decoration: underline;
        }

        .result-url {
            color: #006621;
            font-size: 14px;
            margin-bottom: 8px;
            word-break: break-all;
        }

        .result-description {
            color: #545454;
            line-height: 1.5;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #f5c6cb;
        }

        .no-results {
            text-align: center;
            color: #6c757d;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            color: #6c757d;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .search-input-group {
                flex-direction: column;
            }
            
            .search-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo SITE_TITLE; ?></h1>
            <p><?php echo SITE_DESCRIPTION; ?></p>
        </div>

        <div class="search-form">
            <form id="searchForm">
                <div class="search-input-group">
                    <input 
                        type="text" 
                        id="searchInput" 
                        class="search-input" 
                        placeholder="Enter your search query..."
                        required
                        autocomplete="off"
                    >
                    <button type="submit" class="search-button" id="searchButton">
                        Search
                    </button>
                </div>
            </form>
        </div>

        <div class="loading" id="loading">
            <p>Searching...</p>
        </div>

        <div class="results" id="results">
            <div class="results-header">
                <h2>Search Results</h2>
                <p id="resultsInfo"></p>
            </div>
            <div class="results-container" id="resultsContainer">
                <!-- Results will be populated here -->
            </div>
        </div>

        <div class="footer">
            <p>Powered by DuckDuckGo API | Search queries are logged for research purposes</p>
        </div>
    </div>

    <script>
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            performSearch();
        });

        function performSearch() {
            const query = document.getElementById('searchInput').value.trim();
            
            if (!query) {
                alert('Please enter a search query');
                return;
            }

            // Show loading state
            document.getElementById('loading').style.display = 'block';
            document.getElementById('results').style.display = 'none';
            document.getElementById('searchButton').disabled = true;
            document.getElementById('searchButton').textContent = 'Searching...';

            // Make AJAX request
            fetch('search.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'query=' + encodeURIComponent(query)
            })
            .then(response => response.json())
            .then(data => {
                displayResults(data);
            })
            .catch(error => {
                console.error('Error:', error);
                displayError('An error occurred while searching. Please try again.');
            })
            .finally(() => {
                // Reset loading state
                document.getElementById('loading').style.display = 'none';
                document.getElementById('searchButton').disabled = false;
                document.getElementById('searchButton').textContent = 'Search';
            });
        }

        function displayResults(data) {
            const resultsDiv = document.getElementById('results');
            const resultsContainer = document.getElementById('resultsContainer');
            const resultsInfo = document.getElementById('resultsInfo');

            if (data.error) {
                displayError(data.error);
                return;
            }

            if (!data.results || data.results.length === 0) {
                resultsContainer.innerHTML = '<div class="no-results">No results found for your search query.</div>';
            } else {
                resultsInfo.textContent = `Found ${data.total} result(s) for "${data.query}"`;
                
                resultsContainer.innerHTML = data.results.map(result => `
                    <div class="result-item">
                        <div class="result-title">
                            <a href="${escapeHtml(result.url)}" target="_blank" rel="noopener noreferrer">
                                ${escapeHtml(result.title)}
                            </a>
                        </div>
                        ${result.url !== '#' ? `<div class="result-url">${escapeHtml(result.url)}</div>` : ''}
                        <div class="result-description">
                            ${escapeHtml(result.description)}
                        </div>
                    </div>
                `).join('');
            }

            resultsDiv.style.display = 'block';
        }

        function displayError(message) {
            const resultsDiv = document.getElementById('results');
            const resultsContainer = document.getElementById('resultsContainer');
            
            resultsContainer.innerHTML = `<div class="error">${escapeHtml(message)}</div>`;
            resultsDiv.style.display = 'block';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Focus on search input when page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('searchInput').focus();
        });
    </script>
</body>
</html>