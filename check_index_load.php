<?php
/**
 * Test if index.php loads correctly
 * This simulates what happens when index.php is accessed
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(5);

echo "<h2>Index.php Load Test</h2>";
echo "<pre>";

// Simulate loading index.php
echo "1. Loading index.php content...\n";
$start = microtime(true);
ob_start();
include('index.php');
$content = ob_get_clean();
$loadTime = round((microtime(true) - $start) * 1000, 2);
echo "   ✓ Index.php loaded in {$loadTime}ms\n";
echo "   Content length: " . strlen($content) . " bytes\n\n";

// Test if login pages can be included (they include config)
echo "2. Testing adminlogin.php include...\n";
$start = microtime(true);
try {
    ob_start();
    include('adminlogin.php');
    $adminContent = ob_get_clean();
    $adminTime = round((microtime(true) - $start) * 1000, 2);
    echo "   ✓ Adminlogin.php loaded in {$adminTime}ms\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Summary ===\n";
echo "All pages should load quickly (< 1000ms)\n";
echo "</pre>";

echo "<hr>";
echo "<h3>Actual index.php output:</h3>";
echo substr($content, 0, 500) . "...";
?>

