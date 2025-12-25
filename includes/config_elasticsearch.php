<?php
/**
 * Elasticsearch Configuration File
 * Replaces MySQL connection with Elasticsearch client
 */

// Elasticsearch connection settings
define('ES_HOST', 'localhost');
define('ES_PORT', '9200');
define('ES_SCHEME', 'http'); // http or https

// Elasticsearch index names (using numeric indexes)
define('INDEX_STUDENTS', '1');
define('INDEX_FACULTY', '2');
define('INDEX_CLASSES', '3');
define('INDEX_SUBJECTS', '4');
define('INDEX_RESULTS', '5');
define('INDEX_STUDENT_PERFORMANCE', '6');
define('INDEX_FACULTY_ASSIGNMENTS', '7');
define('INDEX_CURRICULUM_MAPPINGS', '8');
define('INDEX_DEPARTMENTS', '9');
define('INDEX_ADMINISTRATORS', '10');
define('INDEX_SEMESTER_RESULTS', '11');
define('INDEX_SUBJECT_ANALYTICS', '12');
define('INDEX_CLASS_METRICS', '13');
define('INDEX_FACULTY_WORKLOAD', '14');
define('INDEX_STUDENT_SEARCH', '15');
define('INDEX_ANNUAL_RESULTS', '16');
define('INDEX_GRADE_DISTRIBUTION', '17');
define('INDEX_ATTENDANCE', '18');
define('INDEX_FACULTY_METRICS', '19');
define('INDEX_CURRICULUM', '20');
define('INDEX_TRANSCRIPTS', '21');
define('INDEX_AT_RISK_STUDENTS', '22');
define('INDEX_ACADEMIC_RANKINGS', '23');
define('INDEX_SUBJECT_STATISTICS', '24');
define('INDEX_ACADEMIC_EVENTS', '25');
define('INDEX_STUDENT_PROGRESS', '26');
define('INDEX_CLASS_COMPARISONS', '27');
define('INDEX_NOTIFICATIONS', '28');
define('INDEX_AUDIT_LOGS', '29');
define('INDEX_REPORTS', '30');

// Elasticsearch base URL
define('ES_BASE_URL', ES_SCHEME . '://' . ES_HOST . ':' . ES_PORT);

/**
 * Elasticsearch Helper Class
 */
class ElasticsearchClient {
    private $baseUrl;
    
    public function __construct() {
        $this->baseUrl = ES_BASE_URL;
    }
    
    /**
     * Make HTTP request to Elasticsearch
     */
    private function request($method, $endpoint, $data = null) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        if ($ch === false) {
            return ['success' => false, 'error' => ['message' => 'Failed to initialize cURL'], 'http_code' => 0];
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        // Add timeout settings to prevent hanging - very aggressive timeouts
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); // Connection timeout: 1 second
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Total timeout: 3 seconds
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, false);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1); // Prevent signals from interrupting
        
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data))
            ]);
        }
        
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Check for cURL errors - fail fast, don't block
        if ($curlError) {
            error_log("Elasticsearch cURL Error: " . $curlError);
            return ['success' => false, 'error' => ['message' => 'cURL Error: ' . $curlError], 'http_code' => 0];
        }
        
        if ($response === false) {
            error_log("Elasticsearch: No response received");
            return ['success' => false, 'error' => ['message' => 'No response from Elasticsearch'], 'http_code' => 0];
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'data' => $result];
        } else {
            return ['success' => false, 'error' => $result, 'http_code' => $httpCode];
        }
    }
    
    /**
     * Index a document (INSERT/UPDATE)
     */
    public function index($indexName, $id, $document) {
        $endpoint = '/' . $indexName . '/_doc/' . $id;
        return $this->request('PUT', $endpoint, $document);
    }
    
    /**
     * Get a document by ID
     */
    public function get($indexName, $id) {
        $endpoint = '/' . $indexName . '/_doc/' . $id;
        return $this->request('GET', $endpoint);
    }
    
    /**
     * Delete a document by ID
     */
    public function delete($indexName, $id) {
        $endpoint = '/' . $indexName . '/_doc/' . $id;
        return $this->request('DELETE', $endpoint);
    }
    
    /**
     * Search documents
     */
    public function search($indexName, $query) {
        $endpoint = '/' . $indexName . '/_search';
        return $this->request('POST', $endpoint, $query);
    }
    
    /**
     * Bulk operations
     */
    public function bulk($operations) {
        $endpoint = '/_bulk';
        $bulkData = '';
        foreach ($operations as $op) {
            $bulkData .= json_encode($op['action']) . "\n";
            if (isset($op['data'])) {
                $bulkData .= json_encode($op['data']) . "\n";
            }
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $bulkData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-ndjson']);
        
        // Add timeout settings
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Bulk operations timeout
        
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($curlError) {
            return ['errors' => true, 'error' => 'cURL Error: ' . $curlError];
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Check if Elasticsearch is available (non-blocking)
     */
    public function ping() {
        try {
            $result = $this->request('GET', '/');
            return $result['success'];
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if an index exists
     */
    public function indexExists($indexName) {
        // Use GET instead of HEAD for better compatibility
        $result = $this->request('GET', '/' . $indexName);
        return $result['success'] || (isset($result['http_code']) && $result['http_code'] == 200);
    }
    
    /**
     * Make a raw HTTP request (public method for testing)
     */
    public function rawRequest($method, $endpoint, $data = null) {
        return $this->request($method, $endpoint, $data);
    }
}

// Initialize Elasticsearch client (similar to $dbh in MySQL)
// Use lazy initialization to prevent blocking on config load
if (!isset($es)) {
    try {
        $es = new ElasticsearchClient();
    } catch (Exception $e) {
        error_log("Error initializing Elasticsearch client: " . $e->getMessage());
        // Create a dummy object to prevent fatal errors
        $es = null;
    }
}

// Test connection (non-blocking - only log errors, don't fail)
// Commented out to prevent blocking page loads
// Uncomment if you want connection checks
/*
if ($es && !$es->ping()) {
    error_log("Warning: Cannot connect to Elasticsearch at " . ES_BASE_URL);
}
*/

?>

