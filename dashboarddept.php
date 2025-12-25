<?php
session_start();
//error_reporting(0);
include('includes/config_elasticsearch.php');
if(strlen($_SESSION['alogin'])=="")
    {
    header("Location:adminlogin.php");
    }
    else{
        $departmentid = $_SESSION['id'];
        // Get department name from Elasticsearch
        $deptResult = $es->get(INDEX_DEPARTMENTS, $departmentid);
        $departmentname = '';
        if($deptResult['success'] && isset($deptResult['data']['_source'])) {
            $departmentname = $deptResult['data']['_source']['departmentName'];
        }

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
        <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
        </style>
    </head>
    <body class="top-navbar-fixed">
        <div class="main-wrapper">
              <?php include('includes/topbardept.php');?>
            <div class="content-wrapper">
                <div class="content-container">

                    <?php include('includes/leftbardept.php');?>

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
// Get students for this department
$totalstudents = 0;
if($es->indexExists(INDEX_CLASSES) && $es->indexExists(INDEX_STUDENTS)) {
    // Get classes for this department
    $classesQuery = [
        'query' => [
            'term' => ['className' => $departmentname]
        ],
        'size' => 1000
    ];
    $classesResult = $es->search(INDEX_CLASSES, $classesQuery);
    
    $uniqueStudents = [];
    if($classesResult['success'] && isset($classesResult['data']['hits']['hits'])) {
        foreach($classesResult['data']['hits']['hits'] as $classHit) {
            $classId = $classHit['_source']['id'];
            
            // Get students for this class
            $studentsQuery = [
                'query' => [
                    'term' => ['classId' => intval($classId)]
                ],
                'size' => 1000
            ];
            $studentsResult = $es->search(INDEX_STUDENTS, $studentsQuery);
            if($studentsResult['success'] && isset($studentsResult['data']['hits']['hits'])) {
                foreach($studentsResult['data']['hits']['hits'] as $studentHit) {
                    $studentId = $studentHit['_source']['studentId'];
                    if(!isset($uniqueStudents[$studentId])) {
                        $uniqueStudents[$studentId] = true;
                    }
                }
            }
        }
    }
    $totalstudents = count($uniqueStudents);
}
?>

                                            <span class="number counter"><?php echo htmlentities($totalstudents);?> </span>
                                            <span class="name">Users</span>
                                            <span class="bg-icon"><i class="fa fa-users"></i></span>
                                        </a>
                                        <!-- /.dashboard-stat -->
                                    </div>
                                    <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

                                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                                        <a class="dashboard-stat bg-danger" href="manage-subjects.php">
<?php
// Get subjects for this department from curriculum mappings
$totalsubjects = 0;
if($es->indexExists(INDEX_CLASSES) && $es->indexExists(INDEX_CURRICULUM_MAPPINGS)) {
    // Get classes for this department
    $classesQuery = [
        'query' => [
            'term' => ['className' => $departmentname]
        ],
        'size' => 1000
    ];
    $classesResult = $es->search(INDEX_CLASSES, $classesQuery);
    
    $uniqueSubjects = [];
    if($classesResult['success'] && isset($classesResult['data']['hits']['hits'])) {
        foreach($classesResult['data']['hits']['hits'] as $classHit) {
            $classId = $classHit['_source']['id'];
            
            // Get subjects for this class
            $subjectsQuery = [
                'query' => [
                    'term' => ['classId' => intval($classId)]
                ],
                'size' => 1000
            ];
            $subjectsResult = $es->search(INDEX_CURRICULUM_MAPPINGS, $subjectsQuery);
            if($subjectsResult['success'] && isset($subjectsResult['data']['hits']['hits'])) {
                foreach($subjectsResult['data']['hits']['hits'] as $subjectHit) {
                    $subjectId = $subjectHit['_source']['subjectId'];
                    if(!isset($uniqueSubjects[$subjectId])) {
                        $uniqueSubjects[$subjectId] = true;
                    }
                }
            }
        }
    }
    $totalsubjects = count($uniqueSubjects);
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
// Get classes for this department
$totalclasses = 0;
if($es->indexExists(INDEX_CLASSES)) {
    $classesQuery = [
        'query' => [
            'term' => ['className' => $departmentname]
        ],
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
// Calculate pass percentage for this department - optimized
$percentage_grades_greater_than_zero = 0;
if($es->indexExists(INDEX_CLASSES) && $es->indexExists(INDEX_RESULTS)) {
    // Get classes for this department
    $classesQuery = [
        'query' => [
            'term' => ['className' => $departmentname]
        ],
        'size' => 100
    ];
    $classesResult = $es->search(INDEX_CLASSES, $classesQuery);
    
    if($classesResult['success'] && isset($classesResult['data']['hits']['hits'])) {
        // Build list of class IDs
        $classIds = [];
        foreach($classesResult['data']['hits']['hits'] as $classHit) {
            $classIds[] = intval($classHit['_source']['id']);
        }
        
        // Single query to get all results for these classes
        if(!empty($classIds)) {
            $resultsQuery = [
                'query' => [
                    'terms' => ['classId' => $classIds]
                ],
                'size' => 1000
            ];
            $resultsResult = $es->search(INDEX_RESULTS, $resultsQuery);
            
            $totalGrades = 0;
            $passedGrades = 0;
            
            if($resultsResult['success'] && isset($resultsResult['data']['hits']['hits'])) {
                foreach($resultsResult['data']['hits']['hits'] as $resultHit) {
                    $grade = isset($resultHit['_source']['grades']) ? floatval($resultHit['_source']['grades']) : 0;
                    $totalGrades++;
                    if($grade > 0) {
                        $passedGrades++;
                    }
                }
            }
            
            if($totalGrades > 0) {
                $percentage_grades_greater_than_zero = round(($passedGrades * 100.0) / $totalGrades, 2);
            }
        }
    }
}
?>

                                            <span class="number counter"><?php echo htmlentities($percentage_grades_greater_than_zero,2);?></span>
                                            <span class="name">Total Pass Percentage</span>
                                            <span class="bg-icon"><i class="fa fa-file-text"></i></span>
                                        </a>
                                        <!-- /.dashboard-stat -->
                                    </div>
                                    <!-- /.col-lg-3 col-md-3 col-sm-6 col-xs-12 -->

                                </div>
                                <!-- /.row -->
                            </div>
                            <!-- /.container-fluid -->
                            <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="panel">
                                                                        <div class="panel-body">
                                        <div id="chartdiv" style="width: 100%; height: 500px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
        <!--script src="js/amcharts/amcharts.js"></script>
        <script src="js/amcharts/serial.js"></script>
        <script src="js/amcharts/plugins/export/export.min.js"></script>
        <link rel="stylesheet" href="js/amcharts/plugins/export/export.css" type="text/css" media="all" />
        <script src="js/amcharts/themes/light.js"></script-->

        <script src="js/toastr/toastr.min.js"></script>
        <script src="js/icheck/icheck.min.js"></script>
<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/light.js"></script>
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
