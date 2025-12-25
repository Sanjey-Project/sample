<?php
session_start();
error_reporting(0);
include('includes/config_elasticsearch.php');
if(strlen($_SESSION['alogin'])=="")
    {
    header("Location:adminlogin.php");
    }
    else{
        ?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Admin Home</title>
        <link rel="stylesheet" href="css/bootstrap.min.css" media="screen" >
        <link rel="stylesheet" href="css/font-awesome.min.css" media="screen" >
        <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen" >
        <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen" >
        <link rel="stylesheet" href="css/toastr/toastr.min.css" media="screen" >
        <link rel="stylesheet" href="css/icheck/skins/line/blue.css" >
        <link rel="stylesheet" href="css/icheck/skins/line/red.css" >
        <link rel="stylesheet" href="css/icheck/skins/line/green.css" >
        <link rel="stylesheet" href="css/main.css" media="screen" >
        <script src="js/modernizr/modernizr.min.js"></script>
        
        </style>
    </head>
    <body class="top-navbar-fixed">
        <div class="main-wrapper">
              <?php include('includes/topbar.php');?>
            <div class="content-wrapper">
                <div class="content-container">

                    <?php include('includes/leftbar.php');?>

                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-sm-6">
                                    <h2 class="title">Dashboard</h2>

                                </div>
                                <!-- /.col-sm-6 -->
                            </div>
                            <!-- /.row -->

                        </div>
                        <!-- /.container-fluid -->

                        <section class="section">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                        <a class="dashboard-stat bg-primary" href="manage-students.php">
<?php
// Count total students
$totalstudents = 0;
if($es->indexExists(INDEX_STUDENTS)) {
    $studentsQuery = [
        'query' => ['match_all' => []],
        'size' => 0
    ];
    $studentsResult = $es->search(INDEX_STUDENTS, $studentsQuery);
    if($studentsResult['success']) {
        $totalstudents = isset($studentsResult['data']['hits']['total']['value']) ? 
                        $studentsResult['data']['hits']['total']['value'] : 
                        (isset($studentsResult['data']['hits']['total']) ? intval($studentsResult['data']['hits']['total']) : 0);
    }
}
?>

                                            <span class="number counter"><?php echo htmlentities($totalstudents);?></span>
                                            <span class="name">Regd Users</span>
                                            <span class="bg-icon"><i class="fa fa-users"></i></span>
                                        </a>
                                        <!-- /.dashboard-stat -->
                                    </div>
                                    <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

                                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                        <a class="dashboard-stat bg-danger" href="manage-subjects.php">
<?php
// Count total subjects
$totalsubjects = 0;
if($es->indexExists(INDEX_SUBJECTS)) {
    $subjectsQuery = [
        'query' => ['match_all' => []],
        'size' => 0
    ];
    $subjectsResult = $es->search(INDEX_SUBJECTS, $subjectsQuery);
    if($subjectsResult['success']) {
        $totalsubjects = isset($subjectsResult['data']['hits']['total']['value']) ? 
                        $subjectsResult['data']['hits']['total']['value'] : 
                        (isset($subjectsResult['data']['hits']['total']) ? intval($subjectsResult['data']['hits']['total']) : 0);
    }
}
?>
                                            <span class="number counter"><?php echo htmlentities($totalsubjects);?></span>
                                            <span class="name">Subjects Listed</span>
                                            <span class="bg-icon"><i class="fa fa-ticket"></i></span>
                                        </a>
                                        <!-- /.dashboard-stat -->
                                    </div>
                                    <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

                                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                        <a class="dashboard-stat bg-warning" href="manage-classes.php">
                                        <?php
// Count total classes
$totalclasses = 0;
if($es->indexExists(INDEX_CLASSES)) {
    $classesQuery = [
        'query' => ['match_all' => []],
        'size' => 0
    ];
    $classesResult = $es->search(INDEX_CLASSES, $classesQuery);
    if($classesResult['success']) {
        $totalclasses = isset($classesResult['data']['hits']['total']['value']) ? 
                       $classesResult['data']['hits']['total']['value'] : 
                       (isset($classesResult['data']['hits']['total']) ? intval($classesResult['data']['hits']['total']) : 0);
    }
}
?>
                                            <span class="number counter"><?php echo htmlentities($totalclasses);?></span>
                                            <span class="name">Total classes listed</span>
                                            <span class="bg-icon"><i class="fa fa-bank"></i></span>
                                        </a>
                                        <!-- /.dashboard-stat -->
                                    </div>
                                    <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

                                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                        <a class="dashboard-stat bg-success" href="manage-results.php">
                                        <?php
// Count distinct students with results - optimized using aggregation
$totalresults = 0;
if($es->indexExists(INDEX_RESULTS)) {
    $resultsQuery = [
        'query' => ['match_all' => []],
        'size' => 0,
        'aggs' => [
            'unique_students' => [
                'cardinality' => [
                    'field' => 'studentId'
                ]
            ]
        ]
    ];
    $resultsResult = $es->search(INDEX_RESULTS, $resultsQuery);
    if($resultsResult['success']) {
        if(isset($resultsResult['data']['aggregations']['unique_students']['value'])) {
            $totalresults = $resultsResult['data']['aggregations']['unique_students']['value'];
        } else {
            // Fallback: use total hits if aggregation not available
            $totalresults = isset($resultsResult['data']['hits']['total']['value']) ? 
                           $resultsResult['data']['hits']['total']['value'] : 
                           (isset($resultsResult['data']['hits']['total']) ? intval($resultsResult['data']['hits']['total']) : 0);
        }
    }
}
?>

                                            <span class="number counter"><?php echo htmlentities($totalresults);?></span>
                                            <span class="name">Results Declared</span>
                                            <span class="bg-icon"><i class="fa fa-file-text"></i></span>
                                        </a>
                                        <!-- /.dashboard-stat -->
                                    </div>
                                    <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

                                </div>
                                <!-- /.row -->
                            </div>
                            <!-- /.container-fluid -->
                        </section>
                        <!-- /.section -->

                    </div>
                    <!-- /.main-page -->


                </div>
                <!-- /.content-container -->
            </div>
            <!-- /.content-wrapper -->

        </div>
        <!-- /.main-wrapper -->

        <!-- ========== COMMON JS FILES ========== -->
        <script src="js/jquery/jquery-2.2.4.min.js"></script>
        <script src="js/jquery-ui/jquery-ui.min.js"></script>
        <script src="js/bootstrap/bootstrap.min.js"></script>
        <script src="js/pace/pace.min.js"></script>
        <script src="js/lobipanel/lobipanel.min.js"></script>
        <script src="js/iscroll/iscroll.js"></script>

        <!-- ========== PAGE JS FILES ========== -->
        <script src="js/prism/prism.js"></script>
        <script src="js/waypoint/waypoints.min.js"></script>
        <script src="js/counterUp/jquery.counterup.min.js"></script>
        <script src="js/amcharts/amcharts.js"></script>
        <script src="js/amcharts/serial.js"></script>
        <script src="js/amcharts/plugins/export/export.min.js"></script>
        <link rel="stylesheet" href="js/amcharts/plugins/export/export.css" type="text/css" media="all" />
        <script src="js/amcharts/themes/light.js"></script>
        <script src="js/toastr/toastr.min.js"></script>
        <script src="js/icheck/icheck.min.js"></script>

        <!-- ========== THEME JS ========== -->
        <script src="js/main.js"></script>
        <script src="js/production-chart.js"></script>
        <script src="js/traffic-chart.js"></script>
        <script src="js/task-list.js"></script>
        <script>
            $(function(){

                // Counter for dashboard stats
                $('.counter').counterUp({
                    delay: 10,
                    time: 1000
                });

                // Welcome notification
                toastr.options = {
                  "closeButton": true,
                  "debug": false,
                  "newestOnTop": false,
                  "progressBar": false,
                  "positionClass": "toast-top-right",
                  "preventDuplicates": false,
                  "onclick": null,
                  "showDuration": "300",
                  "hideDuration": "1000",
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showEasing": "swing",
                  "hideEasing": "linear",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
                }
                toastr["success"]( "Welcome to Academic Performance Analysis System!");

            });
        </script>
    </body>
</html>
<?php } ?>
