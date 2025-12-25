<?php
/**
 * Create Elasticsearch Indexes
 * This script creates all indexes (1-30) with their mappings from elasticsearch_mappings_numeric.json
 */

include("includes/config_elasticsearch.php");

// Read the mappings file
$mappingsFile = __DIR__ . '/elasticsearch_mappings_numeric.json';
if (!file_exists($mappingsFile)) {
    die("Error: elasticsearch_mappings_numeric.json not found!\n");
}

$mappingsJson = file_get_contents($mappingsFile);
$mappings = json_decode($mappingsJson, true);

if (!$mappings) {
    die("Error: Could not parse mappings JSON file!\n");
}

// Check Elasticsearch connection
if (!$es->ping()) {
    die("Error: Cannot connect to Elasticsearch at " . ES_BASE_URL . "\nPlease ensure Elasticsearch is running.\n");
}

echo "Connected to Elasticsearch successfully!\n\n";

// Create each index
$created = 0;
$skipped = 0;
$errors = 0;

foreach ($mappings as $indexName => $mapping) {
    echo "Creating index '$indexName'... ";
    
    // Check if index already exists
    if ($es->indexExists($indexName)) {
        echo "SKIPPED (already exists)\n";
        $skipped++;
        continue;
    }
    
    // Create index with mapping
    $result = $es->rawRequest('PUT', '/' . $indexName, $mapping);
    
    if ($result['success']) {
        echo "SUCCESS\n";
        $created++;
    } else {
        echo "FAILED\n";
        if (isset($result['error'])) {
            echo "  Error: " . json_encode($result['error']) . "\n";
        }
        $errors++;
    }
}

echo "\n";
echo "Summary:\n";
echo "  Created: $created\n";
echo "  Skipped: $skipped\n";
echo "  Errors: $errors\n";
echo "\nDone!\n";

?>


