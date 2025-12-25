<?php
/**
 * Timeout Diagnostic Tool
 * Helps identify which queries are causing timeouts
 */

include('includes/config_elasticsearch.php');

// Increase PHP execution time for diagnostics
set_time_limit(60);
ini_set('max_execution_time', 60);

echo "<h2>Elasticsearch Timeout Diagnostic</h2>";
echo "<pre>";

// Test each type of query with timing
$tests = [];

// Test 1: Simple GET
echo "Test 1: Simple GET request...\n";
$start = microtime(true);
$result = $es->get(INDEX_STUDENTS, 1);
$time = round((microtime(true) - $start) * 1000, 2);
$tests['GET'] = $time;
echo "   Time: {$time}ms\n";
echo "   Result: " . ($result['success'] ? "✓ Success" : "✗ Failed") . "\n\n";

// Test 2: Small search
echo "Test 2: Small search (size: 10)...\n";
$start = microtime(true);
$query = ['query' => ['match_all' => []], 'size' => 10];
$result = $es->search(INDEX_STUDENTS, $query);
$time = round((microtime(true) - $start) * 1000, 2);
$tests['Small Search'] = $time;
echo "   Time: {$time}ms\n";
echo "   Result: " . ($result['success'] ? "✓ Success" : "✗ Failed") . "\n\n";

// Test 3: Medium search
echo "Test 3: Medium search (size: 100)...\n";
$start = microtime(true);
$query = ['query' => ['match_all' => []], 'size' => 100];
$result = $es->search(INDEX_STUDENTS, $query);
$time = round((microtime(true) - $start) * 1000, 2);
$tests['Medium Search'] = $time;
echo "   Time: {$time}ms\n";
echo "   Result: " . ($result['success'] ? "✓ Success" : "✗ Failed") . "\n\n";

// Test 4: Large search (current problematic size)
echo "Test 4: Large search (size: 1000)...\n";
$start = microtime(true);
$query = ['query' => ['match_all' => []], 'size' => 1000];
$result = $es->search(INDEX_STUDENTS, $query);
$time = round((microtime(true) - $start) * 1000, 2);
$tests['Large Search'] = $time;
echo "   Time: {$time}ms\n";
echo "   Result: " . ($result['success'] ? "✓ Success" : "✗ Failed") . "\n\n";

// Test 5: Count query (size: 0)
echo "Test 5: Count query (size: 0)...\n";
$start = microtime(true);
$query = ['query' => ['match_all' => []], 'size' => 0];
$result = $es->search(INDEX_STUDENTS, $query);
$time = round((microtime(true) - $start) * 1000, 2);
$tests['Count Query'] = $time;
echo "   Time: {$time}ms\n";
echo "   Result: " . ($result['success'] ? "✓ Success" : "✗ Failed") . "\n";
if ($result['success']) {
    $total = isset($result['data']['hits']['total']['value']) ? 
             $result['data']['hits']['total']['value'] : 
             (isset($result['data']['hits']['total']) ? intval($result['data']['hits']['total']) : 0);
    echo "   Total documents: {$total}\n";
}
echo "\n";

// Summary
echo "=== Performance Summary ===\n";
foreach ($tests as $test => $time) {
    $status = $time > 5000 ? "⚠ SLOW" : ($time > 1000 ? "⚠ MODERATE" : "✓ FAST");
    echo sprintf("   %-20s: %6.2fms %s\n", $test, $time, $status);
}

echo "\n=== Recommendations ===\n";
if ($tests['Large Search'] > 5000) {
    echo "⚠ Large searches (>1000 docs) are slow. Consider:\n";
    echo "   - Using pagination (from/size)\n";
    echo "   - Using count queries (size: 0) for totals\n";
    echo "   - Adding filters to reduce result set\n";
}

echo "\n=== PHP Settings ===\n";
echo "   max_execution_time: " . ini_get('max_execution_time') . "s\n";
echo "   memory_limit: " . ini_get('memory_limit') . "\n";
echo "   default_socket_timeout: " . ini_get('default_socket_timeout') . "s\n";

echo "</pre>";
?>

