<?php
/**
 * Quick test to see if pages load
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(10);

echo "<h2>Quick Load Test</h2>";
echo "<pre>";

echo "1. Testing config load...\n";
$start = microtime(true);
include('includes/config_elasticsearch.php');
$configTime = round((microtime(true) - $start) * 1000, 2);
echo "   ✓ Config loaded in {$configTime}ms\n\n";

echo "2. Testing Elasticsearch ping...\n";
$start = microtime(true);
$pingResult = $es->ping();
$pingTime = round((microtime(true) - $start) * 1000, 2);
echo "   " . ($pingResult ? "✓" : "✗") . " Ping completed in {$pingTime}ms\n\n";

echo "3. Testing simple search...\n";
$start = microtime(true);
$query = ['query' => ['match_all' => []], 'size' => 1];
$searchResult = $es->search(INDEX_STUDENTS, $query);
$searchTime = round((microtime(true) - $start) * 1000, 2);
echo "   " . ($searchResult['success'] ? "✓" : "✗") . " Search completed in {$searchTime}ms\n\n";

echo "=== Summary ===\n";
echo "Total time: " . round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . "ms\n";
echo "</pre>";

echo "<p><a href='index.php'>Go to Index Page</a></p>";
?>

