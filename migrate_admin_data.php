<?php
/**
 * Migrate Admin Data from MySQL to Elasticsearch
 * This is an example migration script for administrators
 */

// Include both configs
include("includes/config.php"); // MySQL
include("includes/config_elasticsearch.php"); // Elasticsearch

echo "Starting migration of admin data...\n\n";

// Check connections
if (!$es->ping()) {
    die("Error: Cannot connect to Elasticsearch!\n");
}

// Get all admins from MySQL
try {
    $sql = "SELECT * FROM admindata";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($admins) . " admin(s) in MySQL\n\n";
    
    $migrated = 0;
    $errors = 0;
    
    foreach ($admins as $admin) {
        // Map MySQL fields to Elasticsearch fields
        $document = [
            'id' => intval($admin['id']),
            'fullName' => $admin['FullName'],
            'userName' => $admin['UserName'], // Note: MySQL has UserName, ES expects userName
            'email' => $admin['email'],
            'phno' => intval($admin['Phno']),
            'password' => $admin['Password'],
            'updationDate' => $admin['UpdationDate']
        ];
        
        // Index the document (use id as document ID)
        $result = $es->index(INDEX_ADMINISTRATORS, $admin['id'], $document);
        
        if ($result['success']) {
            echo "✓ Migrated admin: " . $admin['UserName'] . " (ID: " . $admin['id'] . ")\n";
            $migrated++;
        } else {
            echo "✗ Failed to migrate admin: " . $admin['UserName'] . "\n";
            if (isset($result['error'])) {
                echo "  Error: " . json_encode($result['error']) . "\n";
            }
            $errors++;
        }
    }
    
    echo "\nMigration Summary:\n";
    echo "  Migrated: $migrated\n";
    echo "  Errors: $errors\n";
    echo "\nDone!\n";
    
} catch (PDOException $e) {
    die("MySQL Error: " . $e->getMessage() . "\n");
}

?>


