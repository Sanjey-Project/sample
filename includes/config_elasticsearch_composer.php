<?php
/**
 * Elasticsearch Configuration File - Using Official PHP Client
 * Alternative implementation using elasticsearch/elasticsearch package
 * 
 * To use this instead of config_elasticsearch.php:
 * 1. Install: composer require elasticsearch/elasticsearch
 * 2. Replace: include("includes/config_elasticsearch.php");
 *    With: include("includes/config_elasticsearch_composer.php");
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Elasticsearch\ClientBuilder;

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

/**
 * Elasticsearch Client Wrapper (compatible with existing code)
 * Wraps the official Elasticsearch PHP client to match our API
 */
class ElasticsearchClient {
    private $client;
    
    public function __construct() {
        $hosts = [
            [
                'host' => ES_HOST,
                'port' => ES_PORT,
                'scheme' => ES_SCHEME
            ]
        ];
        
        $this->client = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
    }
    
    /**
     * Index a document (INSERT/UPDATE)
     */
    public function index($indexName, $id, $document) {
        try {
            $params = [
                'index' => $indexName,
                'id' => $id,
                'body' => $document
            ];
            
            $response = $this->client->index($params);
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'http_code' => $e->getCode()
            ];
        }
    }
    
    /**
     * Get a document by ID
     */
    public function get($indexName, $id) {
        try {
            $params = [
                'index' => $indexName,
                'id' => $id
            ];
            
            $response = $this->client->get($params);
            
            return [
                'success' => true,
                'data' => ['_source' => $response['_source']]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'http_code' => $e->getCode()
            ];
        }
    }
    
    /**
     * Delete a document by ID
     */
    public function delete($indexName, $id) {
        try {
            $params = [
                'index' => $indexName,
                'id' => $id
            ];
            
            $response = $this->client->delete($params);
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'http_code' => $e->getCode()
            ];
        }
    }
    
    /**
     * Search documents
     */
    public function search($indexName, $query) {
        try {
            $params = [
                'index' => $indexName,
                'body' => $query
            ];
            
            $response = $this->client->search($params);
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'http_code' => $e->getCode()
            ];
        }
    }
    
    /**
     * Check if Elasticsearch is available
     */
    public function ping() {
        try {
            $response = $this->client->ping();
            return $response;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if an index exists
     */
    public function indexExists($indexName) {
        try {
            $params = [
                'index' => $indexName
            ];
            return $this->client->indices()->exists($params);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Make a raw HTTP request (for testing)
     */
    public function rawRequest($method, $endpoint, $data = null) {
        // This is a simplified version - the official client handles this differently
        return $this->ping() ? ['success' => true] : ['success' => false];
    }
}

// Initialize Elasticsearch client (similar to $dbh in MySQL)
$es = new ElasticsearchClient();

// Test connection
if (!$es->ping()) {
    error_log("Warning: Cannot connect to Elasticsearch at " . ES_SCHEME . "://" . ES_HOST . ":" . ES_PORT);
}

?>


