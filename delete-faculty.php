<?php
session_start();
error_reporting(0);
include('includes/config_elasticsearch.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: login.php");
} else {
    if (isset($_GET['facultyid'])) {
        $facultyId = intval($_GET['facultyid']);
        $deleteResult = $es->delete(INDEX_FACULTY, $facultyId);
        
        if ($deleteResult['success']) {
            $msg = "Faculty deleted successfully";
        } else {
            $error = "Failed to delete faculty";
        }
    } else {
        $error = "Faculty ID not provided";
    }

    // Redirect back to the manage classes page with the appropriate message
    header("Location: manage-faculty.php?msg=" . urlencode($msg) . "&error=" . urlencode($error));
    exit();
}
?>
