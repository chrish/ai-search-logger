<?php
/**
 * Configuration file for AI Search Logger
 */

// DuckDuckGo API settings
define('DUCKDUCKGO_API_URL', 'https://api.duckduckgo.com/');

// Search settings
define('MAX_RESULTS', 10);
define('DEFAULT_FORMAT', 'json');

// Logging settings
define('SEARCH_LOG_FILE', '/var/log/apache2/search_queries.log');
define('ENABLE_LOGGING', true);

// Site settings
define('SITE_TITLE', 'AI Search Logger');
define('SITE_DESCRIPTION', 'Search portal for testing AI search behavior');

// Error handling
define('SHOW_ERRORS', false);
error_reporting(SHOW_ERRORS ? E_ALL : 0);
ini_set('display_errors', SHOW_ERRORS ? 1 : 0);

?>