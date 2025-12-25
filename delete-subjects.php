<?php
session_start();
error_reporting(0);
include('includes/config_elasticsearch.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: login.php");
} else {
    if (isset($_GET['subjectid'])) {
        $subjectId = intval($_GET['subjectid']);
        $deleteResult = $es->delete(INDEX_SUBJECTS, $subjectId);
        
        if ($deleteResult['success']) {
            $msg = "Subject deleted successfully";
        } else {
            $error = "Failed to delete subject";
        }
    } else {
        $error = "Subject ID not provided";
    }

    // Redirect back to the manage classes page with the appropriate message
    header("Location: manage-subjects.php?msg=" . urlencode($msg) . "&error=" . urlencode($error));
    exit();
}
?>
