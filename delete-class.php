<?php
session_start();
error_reporting(0);
include('includes/config_elasticsearch.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: login.php");
} else {
    if (isset($_GET['classid'])) {
        $classId = intval($_GET['classid']);
        $deleteResult = $es->delete(INDEX_CLASSES, $classId);
        
        if ($deleteResult['success']) {
            $msg = "Class deleted successfully";
        } else {
            $error = "Failed to delete class";
        }
    } else {
        $error = "Class ID not provided";
    }

    // Redirect back to the manage classes page with the appropriate message
    header("Location: manage-classes.php?msg=" . urlencode($msg) . "&error=" . urlencode($error));
    exit();
}
?>
