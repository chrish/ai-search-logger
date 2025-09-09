# AI Search Logger

A PHP-based search portal that uses the DuckDuckGo API to provide search functionality while logging all search queries for research purposes.

## Features

- Clean, responsive web interface for searching
- Integration with DuckDuckGo Instant Answer API
- Comprehensive logging of search queries with timestamps, IP addresses, and user agents
- Apache configuration for the llm.chrish.no domain
- Security headers and basic protection against common vulnerabilities
- AJAX-based search with real-time results display

## Installation

1. **Web Server Setup**
   ```bash
   # Copy files to web directory
   sudo cp -r . /var/www/ai-search-logger/
   sudo chown -R www-data:www-data /var/www/ai-search-logger/
   sudo chmod -R 755 /var/www/ai-search-logger/
   ```

2. **Apache Configuration**
   ```bash
   # Copy the Apache configuration
   sudo cp apache-config.conf /etc/apache2/sites-available/llm.chrish.no.conf
   sudo a2ensite llm.chrish.no.conf
   
   # Enable required modules
   sudo a2enmod rewrite headers deflate expires ssl
   
   # Restart Apache
   sudo systemctl restart apache2
   ```

3. **Log Directory Setup**
   ```bash
   # Ensure log directory exists and is writable
   sudo mkdir -p /var/log/apache2/
   sudo touch /var/log/apache2/search_queries.log
   sudo chown www-data:www-data /var/log/apache2/search_queries.log
   sudo chmod 644 /var/log/apache2/search_queries.log
   ```

4. **DNS Configuration**
   - Point llm.chrish.no to your server's IP address
   - For HTTPS, obtain and configure SSL certificates

## Configuration

Edit `config.php` to customize:

- `DUCKDUCKGO_API_URL`: DuckDuckGo API endpoint
- `MAX_RESULTS`: Maximum number of search results to display
- `SEARCH_LOG_FILE`: Path to search query log file
- `ENABLE_LOGGING`: Enable/disable search query logging
- `SHOW_ERRORS`: Enable/disable error display (disable in production)

## File Structure

```
ai-search-logger/
├── index.php              # Main search page
├── search.php              # Search handling logic
├── config.php              # Configuration settings
├── .htaccess              # Apache rewrite rules and security
├── apache-config.conf      # Apache virtual host configuration
└── README.md              # This file
```

## API Usage

The search functionality uses the DuckDuckGo Instant Answer API, which:
- Requires no authentication or API keys
- Provides instant answers, definitions, and related topics
- Has rate limiting (please use responsibly)
- Returns JSON responses

## Logging

Search queries are logged in the following format:
```
[2024-01-01 12:00:00] IP: 192.168.1.1 | UA: Mozilla/5.0... | Query: search term
```

Logs include:
- Timestamp
- User IP address
- User Agent string
- Search query

## Security Features

- Input sanitization and validation
- XSS protection headers
- Content type sniffing protection
- Frame options to prevent clickjacking
- File access restrictions via .htaccess
- Error handling without information disclosure

## Testing

1. Start a local PHP server for testing:
   ```bash
   php -S localhost:8000
   ```

2. Open http://localhost:8000 in your browser

3. Test search functionality with various queries

4. Check that search_queries.log is being created and populated

## Production Considerations

- Implement rate limiting to prevent abuse
- Consider using a more comprehensive search API for production
- Set up log rotation for search query logs
- Monitor disk space usage for logs
- Consider implementing caching for frequently searched terms
- Add proper SSL/TLS configuration
- Implement proper backup procedures for logs

## Dependencies

- PHP 7.4+ or 8.0+
- Apache 2.4+ with mod_rewrite, mod_headers
- Internet connection for DuckDuckGo API access

## License

This project is created for research and testing purposes.
