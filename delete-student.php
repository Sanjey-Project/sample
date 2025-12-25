<?php
session_start();
error_reporting(0);
include('includes/config_elasticsearch.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: login.php");
} else {
    if (isset($_GET['stid'])) {
        $studentId = intval($_GET['stid']);
        $deleteResult = $es->delete(INDEX_STUDENTS, $studentId);
        if ($deleteResult['success']) {
            $msg = "Student deleted successfully";
        } else {
            $error = "Failed to delete student";
        }
    } else {
        $error = "Student ID not provided";
    }

    // Redirect back to the manage classes page with the appropriate message
    header("Location: manage-students.php?msg=" . urlencode($msg) . "&error=" . urlencode($error));
    exit();
}
?>
