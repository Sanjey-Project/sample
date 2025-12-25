<?php
/**
 * Check PHP Requirements for Elasticsearch Connection
 */

echo "<h2>PHP Requirements Check for Elasticsearch</h2>";

// Check PHP version
echo "<h3>1. PHP Version</h3>";
echo "Current PHP Version: " . PHP_VERSION . "<br>";
if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
    echo "<span style='color: green;'>✓ PHP version is OK</span><br>";
} else {
    echo "<span style='color: red;'>✗ PHP 7.2+ required</span><br>";
}

// Check cURL extension
echo "<h3>2. cURL Extension</h3>";
if (extension_loaded('curl')) {
    echo "<span style='color: green;'>✓ cURL extension is loaded</span><br>";
    $curlVersion = curl_version();
    echo "cURL Version: " . $curlVersion['version'] . "<br>";
} else {
    echo "<span style='color: red;'>✗ cURL extension is NOT loaded</span><br>";
    echo "<strong>Solution:</strong> Enable cURL extension in php.ini<br>";
    echo "Edit php.ini and uncomment: <code>extension=curl</code><br>";
}

// Check JSON extension
echo "<h3>3. JSON Extension</h3>";
if (extension_loaded('json')) {
    echo "<span style='color: green;'>✓ JSON extension is loaded</span><br>";
} else {
    echo "<span style='color: red;'>✗ JSON extension is NOT loaded</span><br>";
}

// Test Elasticsearch connection
echo "<h3>4. Elasticsearch Connection Test</h3>";
include("includes/config_elasticsearch.php");

if ($es->ping()) {
    echo "<span style='color: green;'>✓ Successfully connected to Elasticsearch at " . ES_BASE_URL . "</span><br>";
    
    // Get cluster info
    $info = $es->rawRequest('GET', '/');
    if ($info['success']) {
        echo "<pre>";
        echo "Cluster Name: " . ($info['data']['cluster_name'] ?? 'N/A') . "\n";
        echo "Version: " . ($info['data']['version']['number'] ?? 'N/A') . "\n";
        echo "</pre>";
    }
} else {
    echo "<span style='color: red;'>✗ Cannot connect to Elasticsearch at " . ES_BASE_URL . "</span><br>";
    echo "<strong>Possible issues:</strong><br>";
    echo "<ul>";
    echo "<li>Elasticsearch is not running</li>";
    echo "<li>Wrong host/port in config_elasticsearch.php</li>";
    echo "<li>Firewall blocking connection</li>";
    echo "<li>cURL cannot make HTTP requests</li>";
    echo "</ul>";
}

// Test cURL functionality
echo "<h3>5. cURL Functionality Test</h3>";
$testUrl = ES_BASE_URL;
$ch = curl_init($testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($httpCode == 200) {
    echo "<span style='color: green;'>✓ cURL can connect to Elasticsearch</span><br>";
} else {
    echo "<span style='color: red;'>✗ cURL connection failed</span><br>";
    if ($curlError) {
        echo "Error: " . htmlspecialchars($curlError) . "<br>";
    }
    echo "HTTP Code: " . $httpCode . "<br>";
}

echo "<hr>";
echo "<p><a href='adminlogin.php'>Back to Admin Login</a> | <a href='test_elasticsearch.php'>Test Elasticsearch</a></p>";
?>


