<?php
session_start();
error_reporting(0);
include('includes/config_elasticsearch.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: login.php");
} else {
    if (isset($_GET['stid'])) {
        $stId = intval($_GET['stid']);
        
        // Get all results for this student
        $resultsQuery = [
            'query' => [
                'term' => ['studentId' => $stId]
            ],
            'size' => 1000
        ];
        $resultsResult = $es->search(INDEX_RESULTS, $resultsQuery);
        
        $deletedCount = 0;
        if($resultsResult['success'] && isset($resultsResult['data']['hits']['hits'])) {
            foreach($resultsResult['data']['hits']['hits'] as $hit) {
                $resultId = $hit['_source']['id'];
                $deleteResult = $es->delete(INDEX_RESULTS, $resultId);
                if($deleteResult['success']) {
                    $deletedCount++;
                }
            }
        }
        
        if ($deletedCount > 0) {
            $msg = "Result deleted successfully";
        } else {
            $error = "Failed to delete result";
        }
    } else {
        $error = "Student ID not provided";
    }

    // Redirect back to the manage classes page with the appropriate message
    header("Location: manage-results.php?msg=" . urlencode($msg) . "&error=" . urlencode($error));
    exit();
}
?>
