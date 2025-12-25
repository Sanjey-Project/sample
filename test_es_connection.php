<?php
/**
 * Quick Elasticsearch Connection Test
 * Use this to diagnose connectivity issues
 */

include('includes/config_elasticsearch.php');

echo "<h2>Elasticsearch Connection Test</h2>";
echo "<pre>";

// Test 1: Basic ping
echo "1. Testing Elasticsearch connection...\n";
$start = microtime(true);
$pingResult = $es->ping();
$pingTime = round((microtime(true) - $start) * 1000, 2);
if ($pingResult) {
    echo "   ✓ Connected successfully (took {$pingTime}ms)\n";
} else {
    echo "   ✗ Connection failed!\n";
    echo "   Check if Elasticsearch is running: curl http://localhost:9200\n";
    exit;
}

// Test 2: Get cluster info
echo "\n2. Getting cluster information...\n";
$start = microtime(true);
$clusterInfo = $es->rawRequest('GET', '/');
$clusterTime = round((microtime(true) - $start) * 1000, 2);
if ($clusterInfo['success']) {
    echo "   ✓ Cluster info retrieved (took {$clusterTime}ms)\n";
    echo "   Cluster Name: " . ($clusterInfo['data']['cluster_name'] ?? 'N/A') . "\n";
    echo "   Version: " . ($clusterInfo['data']['version']['number'] ?? 'N/A') . "\n";
} else {
    echo "   ✗ Failed to get cluster info\n";
}

// Test 3: Check if indexes exist
echo "\n3. Checking indexes...\n";
$indexes = [
    'INDEX_STUDENTS' => INDEX_STUDENTS,
    'INDEX_FACULTY' => INDEX_FACULTY,
    'INDEX_CLASSES' => INDEX_CLASSES,
    'INDEX_SUBJECTS' => INDEX_SUBJECTS,
    'INDEX_RESULTS' => INDEX_RESULTS,
    'INDEX_ADMINISTRATORS' => INDEX_ADMINISTRATORS
];

foreach ($indexes as $name => $index) {
    $start = microtime(true);
    $exists = $es->indexExists($index);
    $existsTime = round((microtime(true) - $start) * 1000, 2);
    $status = $exists ? "✓" : "✗";
    echo "   {$status} {$name} ({$index}): " . ($exists ? "Exists" : "Not found") . " (took {$existsTime}ms)\n";
}

// Test 4: Simple search query
echo "\n4. Testing search query...\n";
$start = microtime(true);
$testQuery = [
    'query' => ['match_all' => []],
    'size' => 1
];
$searchResult = $es->search(INDEX_STUDENTS, $testQuery);
$searchTime = round((microtime(true) - $start) * 1000, 2);
if ($searchResult['success']) {
    echo "   ✓ Search query successful (took {$searchTime}ms)\n";
} else {
    echo "   ✗ Search query failed\n";
    if (isset($searchResult['error'])) {
        echo "   Error: " . json_encode($searchResult['error'], JSON_PRETTY_PRINT) . "\n";
    }
}

// Test 5: Check PHP settings
echo "\n5. PHP Configuration:\n";
echo "   max_execution_time: " . ini_get('max_execution_time') . " seconds\n";
echo "   memory_limit: " . ini_get('memory_limit') . "\n";
echo "   curl extension: " . (extension_loaded('curl') ? "✓ Loaded" : "✗ Not loaded") . "\n";

echo "\n=== Test Complete ===\n";
echo "</pre>";

?>

