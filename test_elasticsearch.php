<?php
/**
 * Elasticsearch Connection Test Page
 * Use this to diagnose Elasticsearch connection issues
 */
include("includes/config_elasticsearch.php");

echo "<h2>Elasticsearch Connection Test</h2>";

// Test 1: Ping Elasticsearch
echo "<h3>1. Testing Elasticsearch Connection</h3>";
$pingResult = $es->ping();
if ($pingResult) {
    echo "<p style='color: green;'>✓ Elasticsearch is accessible at " . ES_BASE_URL . "</p>";
} else {
    echo "<p style='color: red;'>✗ Cannot connect to Elasticsearch at " . ES_BASE_URL . "</p>";
    echo "<p>Please ensure Elasticsearch is running and accessible.</p>";
    exit;
}

// Test 2: Check if index exists
echo "<h3>2. Checking if Index '10' (Administrators) exists</h3>";
$indexExists = $es->indexExists('10');
if ($indexExists) {
    echo "<p style='color: green;'>✓ Index '10' exists</p>";
} else {
    echo "<p style='color: red;'>✗ Index '10' does not exist</p>";
    echo "<p>You need to create the index first using the mapping from elasticsearch_mappings_numeric.json</p>";
    echo "<p>You can create it using:</p>";
    echo "<pre>curl -X PUT \"localhost:9200/10\" -H 'Content-Type: application/json' -d @elasticsearch_mappings_numeric.json</pre>";
}

// Test 3: Try to search for administrators
echo "<h3>3. Testing Search Query</h3>";
$testQuery = [
    'query' => [
        'match_all' => []
    ],
    'size' => 5
];

$searchResult = $es->search(INDEX_ADMINISTRATORS, $testQuery);
if ($searchResult['success']) {
    $total = isset($searchResult['data']['hits']['total']['value']) ? 
             $searchResult['data']['hits']['total']['value'] : 
             (isset($searchResult['data']['hits']['total']) ? $searchResult['data']['hits']['total'] : 0);
    echo "<p style='color: green;'>✓ Search query successful. Found " . $total . " document(s)</p>";
    
    if ($total > 0 && isset($searchResult['data']['hits']['hits'])) {
        echo "<h4>Sample Documents:</h4>";
        echo "<pre>";
        foreach ($searchResult['data']['hits']['hits'] as $hit) {
            echo "ID: " . $hit['_id'] . "\n";
            echo "Source: " . json_encode($hit['_source'], JSON_PRETTY_PRINT) . "\n\n";
        }
        echo "</pre>";
    } else {
        echo "<p style='color: orange;'>⚠ Index exists but contains no documents. You need to migrate data from MySQL.</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Search query failed</p>";
    echo "<pre>Error: " . json_encode($searchResult, JSON_PRETTY_PRINT) . "</pre>";
}

// Test 4: Test specific login query
echo "<h3>4. Testing Login Query (username: 'admin', password: 'Test@123')</h3>";
$loginQuery = [
    'query' => [
        'bool' => [
            'must' => [
                ['term' => ['userName' => 'admin']],
                ['term' => ['password' => 'Test@123']]
            ]
        ]
    ],
    'size' => 1
];

$loginResult = $es->search(INDEX_ADMINISTRATORS, $loginQuery);
if ($loginResult['success']) {
    $total = isset($loginResult['data']['hits']['total']['value']) ? 
             $loginResult['data']['hits']['total']['value'] : 
             (isset($loginResult['data']['hits']['total']) ? $loginResult['data']['hits']['total'] : 0);
    
    if ($total > 0) {
        echo "<p style='color: green;'>✓ Found matching admin user</p>";
        echo "<pre>" . json_encode($loginResult['data']['hits']['hits'][0]['_source'], JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p style='color: orange;'>⚠ No admin found with username 'admin' and password 'Test@123'</p>";
        echo "<p>This could mean:</p>";
        echo "<ul>";
        echo "<li>The credentials don't exist in Elasticsearch</li>";
        echo "<li>The field names don't match (check userName vs UserName)</li>";
        echo "<li>Data hasn't been migrated from MySQL yet</li>";
        echo "</ul>";
    }
} else {
    echo "<p style='color: red;'>✗ Login query failed</p>";
    echo "<pre>Error: " . json_encode($loginResult, JSON_PRETTY_PRINT) . "</pre>";
}

echo "<hr>";
echo "<p><a href='adminlogin.php'>Back to Admin Login</a></p>";
?>

