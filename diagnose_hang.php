<?php
/**
 * Diagnose why pages are hanging
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(5);

echo "<h2>Hang Diagnostic</h2>";
echo "<pre>";

echo "1. PHP Version: " . PHP_VERSION . "\n";
echo "2. Max execution time: " . ini_get('max_execution_time') . "s\n";
echo "3. Memory limit: " . ini_get('memory_limit') . "\n";
echo "4. cURL loaded: " . (extension_loaded('curl') ? 'Yes' : 'No') . "\n\n";

echo "5. Testing Elasticsearch connection (with timeout)...\n";
$start = microtime(true);

// Test direct curl to Elasticsearch
$ch = curl_init('http://localhost:9200');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$time = round((microtime(true) - $start) * 1000, 2);
echo "   Time: {$time}ms\n";
if ($curlError) {
    echo "   Error: {$curlError}\n";
} else {
    echo "   HTTP Code: {$httpCode}\n";
    echo "   Response: " . substr($response, 0, 100) . "...\n";
}

echo "\n6. Testing config load...\n";
$start = microtime(true);
try {
    include('includes/config_elasticsearch.php');
    $time = round((microtime(true) - $start) * 1000, 2);
    echo "   Config loaded in: {$time}ms\n";
    echo "   ES object exists: " . (isset($es) ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n=== Diagnostic Complete ===\n";
echo "</pre>";
?>

