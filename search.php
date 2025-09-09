<?php
require_once 'config.php';

/**
 * Search functionality for DuckDuckGo API
 */
class SearchHandler {
    
    /**
     * Log search query to file
     */
    public function logSearch($query, $userAgent = '', $ip = '') {
        if (!ENABLE_LOGGING) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = sprintf(
            "[%s] IP: %s | UA: %s | Query: %s\n",
            $timestamp,
            $ip ?: $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $userAgent ?: $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            $query
        );
        
        // Try to write to the log file, fallback to local log if permission denied
        $logFile = is_writable(dirname(SEARCH_LOG_FILE)) ? SEARCH_LOG_FILE : './search_queries.log';
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Search using DuckDuckGo Instant Answer API
     */
    public function search($query) {
        if (empty($query)) {
            return ['error' => 'Search query cannot be empty'];
        }
        
        // Log the search
        $this->logSearch($query);
        
        // Try to get results from DuckDuckGo API
        $apiResults = $this->fetchFromDuckDuckGo($query);
        
        // If API fails, fall back to demonstration results
        if (isset($apiResults['error'])) {
            return $this->generateDemoResults($query);
        }
        
        return $apiResults;
    }
    
    /**
     * Fetch results from DuckDuckGo API
     */
    private function fetchFromDuckDuckGo($query) {
        // Prepare API request
        $params = [
            'q' => $query,
            'format' => DEFAULT_FORMAT,
            'no_html' => '1',
            'skip_disambig' => '1'
        ];
        
        $url = DUCKDUCKGO_API_URL . '?' . http_build_query($params);
        
        // Make API request with timeout
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'user_agent' => 'AI Search Logger/1.0',
                'ignore_errors' => true
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            return ['error' => 'API temporarily unavailable'];
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid API response'];
        }
        
        return $this->formatResults($data, $query);
    }
    
    /**
     * Format search results for display
     */
    private function formatResults($data, $query) {
        $results = [];
        
        // DuckDuckGo Instant Answer API returns different types of results
        
        // Abstract (summary)
        if (!empty($data['Abstract'])) {
            $results[] = [
                'title' => $data['Heading'] ?: 'Summary',
                'url' => $data['AbstractURL'] ?: '#',
                'description' => $data['Abstract']
            ];
        }
        
        // Definition
        if (!empty($data['Definition'])) {
            $results[] = [
                'title' => 'Definition',
                'url' => $data['DefinitionURL'] ?: '#',
                'description' => $data['Definition']
            ];
        }
        
        // Related topics
        if (!empty($data['RelatedTopics'])) {
            foreach (array_slice($data['RelatedTopics'], 0, MAX_RESULTS - count($results)) as $topic) {
                if (is_array($topic) && !empty($topic['Text'])) {
                    $results[] = [
                        'title' => $this->extractTitle($topic['Text']),
                        'url' => $topic['FirstURL'] ?: '#',
                        'description' => $topic['Text']
                    ];
                }
            }
        }
        
        // Answer (for calculations, conversions, etc.)
        if (!empty($data['Answer'])) {
            $results[] = [
                'title' => 'Answer',
                'url' => '#',
                'description' => $data['Answer']
            ];
        }
        
        // If no results found, create a mock result for demonstration
        if (empty($results)) {
            $results = $this->generateMockResults($query);
        }
        
        return [
            'results' => array_slice($results, 0, MAX_RESULTS),
            'query' => $query,
            'total' => count($results)
        ];
    }
    
    /**
     * Extract title from text (first sentence or up to 60 chars)
     */
    private function extractTitle($text) {
        $title = strtok($text, '.');
        return strlen($title) > 60 ? substr($title, 0, 57) . '...' : $title;
    }
    
    /**
     * Generate demo results for when API is unavailable
     */
    private function generateDemoResults($query) {
        return [
            'results' => [
                [
                    'title' => 'Search Result for: ' . htmlspecialchars($query),
                    'url' => 'https://duckduckgo.com/?q=' . urlencode($query),
                    'description' => 'This is a demonstration result for the query "' . htmlspecialchars($query) . '". The search portal is working correctly and logging your search. In a production environment with API access, you would see real search results here.'
                ],
                [
                    'title' => 'AI Search Logger - Demo Mode',
                    'url' => '#',
                    'description' => 'The search portal is currently running in demo mode. All search queries are being logged as intended for research purposes. The DuckDuckGo API may be temporarily unavailable or restricted in this environment.'
                ],
                [
                    'title' => 'DuckDuckGo Search',
                    'url' => 'https://duckduckgo.com/?q=' . urlencode($query),
                    'description' => 'Click here to search for "' . htmlspecialchars($query) . '" directly on DuckDuckGo.'
                ],
                [
                    'title' => 'Query Analysis: ' . htmlspecialchars($query),
                    'url' => '#',
                    'description' => 'Your search query contains ' . str_word_count($query) . ' words and ' . strlen($query) . ' characters. This information is being logged for research into AI search behavior patterns.'
                ],
                [
                    'title' => 'Search Statistics',
                    'url' => '#',
                    'description' => 'Search performed at ' . date('Y-m-d H:i:s T') . '. User agent and IP address have been logged for analysis purposes.'
                ]
            ],
            'query' => $query,
            'total' => 5
        ];
    }
    
    /**
     * Generate mock results for demonstration when API doesn't return results
     */
    private function generateMockResults($query) {
        return $this->generateDemoResults($query);
    }
}

// Handle AJAX search requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['query'])) {
    header('Content-Type: application/json');
    
    $searchHandler = new SearchHandler();
    $results = $searchHandler->search($_POST['query']);
    
    echo json_encode($results);
    exit;
}
?>