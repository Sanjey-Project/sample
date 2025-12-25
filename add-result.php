<?php
session_start();
error_reporting(0);
include('includes/config_elasticsearch.php');
require 'excelReader/excel_reader2.php';
require 'excelReader/SpreadsheetReader.php';

if(strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    if(isset($_POST['submit'])) {
        $marks = $_POST['marks'];
        $class = $_POST['class'];
        $studentid = $_POST['studentid'];

        // Get subjects for the class from curriculum mappings
        $subjectQuery = [
            'query' => [
                'term' => ['classId' => intval($class)]
            ],
            'sort' => [['subjectName.sortable' => 'asc']],
            'size' => 500
        ];
        $subjectResult = $es->search(INDEX_CURRICULUM_MAPPINGS, $subjectQuery);
        $sid1 = array();

        if($subjectResult['success'] && isset($subjectResult['data']['hits']['hits'])) {
            foreach($subjectResult['data']['hits']['hits'] as $hit) {
                $row = $hit['_source'];
                array_push($sid1, $row['subjectId']);
            }
        }

        for($i = 0; $i < count($marks); $i++) {
            $mark = $marks[$i];
            $sid = $sid1[$i];
            
            $grades = convertGradeToMarks($mark); // Convert grade to marks

            // Get next available result ID
            $newResultId = 1;
            if($es->indexExists(INDEX_RESULTS)) {
                $maxIdQuery = [
                    'query' => ['match_all' => []],
                    'sort' => [['id' => ['order' => 'desc']]],
                    'size' => 1
                ];
                $maxIdResult = $es->search(INDEX_RESULTS, $maxIdQuery);
                if($maxIdResult['success'] && isset($maxIdResult['data']['hits']['hits'][0])) {
                    $maxId = $maxIdResult['data']['hits']['hits'][0]['_source']['id'];
                    $newResultId = intval($maxId) + 1;
                }
            }

            // Prepare result document
            $resultDoc = [
                'id' => $newResultId,
                'studentId' => intval($studentid),
                'classId' => intval($class),
                'subjectId' => intval($sid),
                'grades' => $grades,
                'marks' => $mark,
                'creationDate' => date('Y-m-d H:i:s')
            ];

            $insertResult = $es->index(INDEX_RESULTS, $newResultId, $resultDoc);
            if($insertResult['success']) {
                $msg = "Result info added successfully";
            } else {
                $error = "Something went wrong. Please try again";
            }
        }
    }
    else if (isset($_POST['importExcel'])) {
        // Handle Excel file import
        $filename = $_FILES['excelFile']['name'];
        $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Check file extension (only allow .xls and .xlsx)
        if (in_array($fileExtension, ['xls', 'xlsx'])) {
            $targetDirectory = "includes/" . $filename;
            move_uploaded_file($_FILES['excelFile']['tmp_name'], $targetDirectory);
    
            $reader = new SpreadsheetReader($targetDirectory);
            $headerRow = $reader->current();
            foreach(array_slice($headerRow,1) as $subjectcode){
            if(!empty($subjectcode))
            {
                // Check if subject exists in Elasticsearch
                if($es->indexExists(INDEX_SUBJECTS)) {
                    $checkQuery = [
                        'query' => [
                            'term' => ['subjectCode' => $subjectcode]
                        ],
                        'size' => 1
                    ];
                    $checkResult = $es->search(INDEX_SUBJECTS, $checkQuery);
                    
                    $subjectExists = false;
                    if($checkResult['success']) {
                        $totalHits = isset($checkResult['data']['hits']['total']['value']) ? 
                                     $checkResult['data']['hits']['total']['value'] : 
                                     (isset($checkResult['data']['hits']['total']) ? intval($checkResult['data']['hits']['total']) : 0);
                        if($totalHits > 0) {
                            $subjectExists = true;
                        }
                    }
    
                    if (!$subjectExists) {
                        // Subject code doesn't exist, insert it
                        $newSubjectId = 1;
                        $maxIdQuery = [
                            'query' => ['match_all' => []],
                            'sort' => [['id' => ['order' => 'desc']]],
                            'size' => 1
                        ];
                        $maxIdResult = $es->search(INDEX_SUBJECTS, $maxIdQuery);
                        if($maxIdResult['success'] && isset($maxIdResult['data']['hits']['hits'][0])) {
                            $maxId = $maxIdResult['data']['hits']['hits'][0]['_source']['id'];
                            $newSubjectId = intval($maxId) + 1;
                        }
                        
                        $subjectDoc = [
                            'id' => $newSubjectId,
                            'subjectCode' => $subjectcode,
                            'subjectName' => $subjectcode, // Default name
                            'credit' => 0,
                            'semester' => 0,
                            'creationDate' => date('Y-m-d H:i:s')
                        ];
                        $es->index(INDEX_SUBJECTS, $newSubjectId, $subjectDoc);
                    }
                }
            }
        }
        foreach($reader as $key =>$row)
        {
            $rollno = $row[0];
            // Get student by rollId
            $studentQuery = [
                'query' => [
                    'term' => ['rollId' => $rollno]
                ],
                'size' => 1
            ];
            $studentResult = $es->search(INDEX_STUDENTS, $studentQuery);
            
            if($studentResult['success'] && isset($studentResult['data']['hits']['hits'][0])) {
                $studentData = $studentResult['data']['hits']['hits'][0]['_source'];
                $rollid = $studentData['studentId'];
                $classid = $studentData['classId'];
            } else {
                continue; // Skip if student not found
            }
    
            foreach(array_slice($row,1) as $index =>$grade)
            {
                if(!empty($grade))
                {
                    $mark = convertGradeToMarks($grade);
                    $subjectcode = $headerRow[$index + 1]; // Adjust index for subject codes starting from second column
                    
                    // Get subject ID by subject code
                    $subjectQuery = [
                        'query' => [
                            'term' => ['subjectCode' => $subjectcode]
                        ],
                        'size' => 1
                    ];
                    $subjectResult = $es->search(INDEX_SUBJECTS, $subjectQuery);
                    
                    if($subjectResult['success'] && isset($subjectResult['data']['hits']['hits'][0])) {
                        $subjectData = $subjectResult['data']['hits']['hits'][0]['_source'];
                        $subjectid = $subjectData['id'];
                        
                        // Get next available result ID
                        $newResultId = 1;
                        if($es->indexExists(INDEX_RESULTS)) {
                            $maxIdQuery = [
                                'query' => ['match_all' => []],
                                'sort' => [['id' => ['order' => 'desc']]],
                                'size' => 1
                            ];
                            $maxIdResult = $es->search(INDEX_RESULTS, $maxIdQuery);
                            if($maxIdResult['success'] && isset($maxIdResult['data']['hits']['hits'][0])) {
                                $maxId = $maxIdResult['data']['hits']['hits'][0]['_source']['id'];
                                $newResultId = intval($maxId) + 1;
                            }
                        }
                        
                        $resultDoc = [
                            'id' => $newResultId,
                            'studentId' => intval($rollid),
                            'classId' => intval($classid),
                            'subjectId' => intval($subjectid),
                            'marks' => $grade,
                            'grades' => $mark,
                            'creationDate' => date('Y-m-d H:i:s')
                        ];
                        
                        $es->index(INDEX_RESULTS, $newResultId, $resultDoc);
                    }
                }
                else
                {
                    break;
                }
            }
        }
            $msg = "Combination Imported successfully";
        } else {
            $error = "Invalid file format. Please upload an Excel file (.xls or .xlsx)";
        }
    }
    
}

function convertGradeToMarks($mark) {
    // Define your conversion rules here
    switch($mark) {
        case 'O':
            return 10;
        case 'A+':
            return 9;
        case 'A':
            return 8;
        case 'B+':
            return 7;
        case 'B':
            return 6;
        // Add more cases for other grades as needed
        default:
            return 0; // Default to 0 if grade not found
    }
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SMS Admin| Add Result </title>
        <link rel="stylesheet" href="css/bootstrap.min.css" media="screen" >
        <link rel="stylesheet" href="css/font-awesome.min.css" media="screen" >
        <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen" >
        <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen" >
        <link rel="stylesheet" href="css/prism/prism.css" media="screen" >
        <link rel="stylesheet" href="css/select2/select2.min.css" >
        <link rel="stylesheet" href="css/main.css" media="screen" >
        <script src="js/modernizr/modernizr.min.js"></script>
        <script>
function getStudent(val) {
    $.ajax({
    type: "POST",
    url: "get_student.php",
    data:'classid='+val,
    success: function(data){
        $("#studentid").html(data);
        
    }
    });
$.ajax({
        type: "POST",
        url: "get_student.php",
        data:'classid1='+val,
        success: function(data){
            $("#subject").html(data);
            
        }
        });
}
    </script>
<script>

function getresult(val,clid) 
{   
    
var clid=$(".clid").val();
var val=$(".stid").val();;
var abh=clid+'$'+val;
//alert(abh);
    $.ajax({
        type: "POST",
        url: "get_student.php",
        data:'studclass='+abh,
        success: function(data){
            $("#reslt").html(data);
            
        }
        });
}
</script>


    </head>
    <body class="top-navbar-fixed">
        <div class="main-wrapper">

            <!-- ========== TOP NAVBAR ========== -->
  <?php include('includes/topbar.php');?> 
            <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
            <div class="content-wrapper">
                <div class="content-container">

                    <!-- ========== LEFT SIDEBAR ========== -->
                   <?php include('includes/leftbar.php');?>  
                    <!-- /.left-sidebar -->

                    <div class="main-page">

                     <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-6">
                                    <h2 class="title">Declare Result</h2>
                                
                                </div>
                                
                                <!-- /.col-md-6 text-right -->
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                
                                        <li class="active">Student Result</li>
                                    </ul>
                                </div>
                             
                            </div>
                            <!-- /.row -->
                        </div>
                        <div class="container-fluid">
                           
                        <div class="row">
                                    <div class="col-md-12">
                                        <div class="panel">
                                           
                                            <div class="panel-body">
<?php if($msg){?>
<div class="alert alert-success left-icon-alert" role="alert">
 <strong>Well done!</strong><?php echo htmlentities($msg); ?>
 </div><?php } 
else if($error){?>
    <div class="alert alert-danger left-icon-alert" role="alert">
                                            <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                        </div>
                                        <?php } ?>
                                                <form class="form-horizontal" method="post">

 <div class="form-group">
<label for="default" class="col-sm-2 control-label">Class</label>
 <div class="col-sm-10">
 <select name="class" class="form-control clid" id="classid" onChange="getStudent(this.value);" required="required">
<option value="">Select Class</option>
<?php 
if($es->indexExists(INDEX_CLASSES)) {
    $classQuery = [
        'query' => ['match_all' => []],
        'sort' => [['className.sortable' => 'asc'], ['classNameNumeric' => 'asc'], ['section' => 'asc']],
        'size' => 500
    ];
    $classResult = $es->search(INDEX_CLASSES, $classQuery);
    if($classResult['success'] && isset($classResult['data']['hits']['hits'])) {
        foreach($classResult['data']['hits']['hits'] as $hit) {
            $result = $hit['_source'];
            ?>
            <option value="<?php echo htmlentities($result['id']); ?>"><?php echo htmlentities($result['className']); ?>&nbsp;<?php echo htmlentities($result['classNameNumeric']); ?>&nbsp; Section-<?php echo htmlentities($result['section']); ?></option>
            <?php 
        }
    }
}
?>
 </select>
                                                        </div>
                                                    </div>
<div class="form-group">
                                                        <label for="date" class="col-sm-2 control-label ">Student Name</label>
                                                        <div class="col-sm-10">
                                                    <select name="studentid" class="form-control stid" id="studentid" required="required" onChange="getresult(this.value);">
                                                    </select>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                      
                                                        <div class="col-sm-10">
                                                    <div  id="reslt">
                                                    </div>
                                                        </div>
                                                    </div>
                                                    
<div class="form-group">
                                                        <label for="date" class="col-sm-2 control-label">Subjects</label>
                                                        <div class="col-sm-10">
                                                    <div  id="subject">
                                                    </div>
                                                        </div>
                                                    </div>


                                                    
                                                    <div class="form-group">
                                                        <div class="col-sm-offset-2 col-sm-10">
                                                            <button type="submit" name="submit" id="submit" class="btn btn-primary">Declare Result</button>
                                                        </div>
                                                    </div>
                                                </form>
                                                <form method="post" enctype="multipart/form-data">
    <!-- File upload field -->
    <div class="form-group has-success">
        <label for="excelFile" class="control-label">Multiple Uploads ? Click Below</label>
        <input type="file" name="excelFile" id="excelFile" class="form-control" accept=".xls,.xlsx">
        <span class="help-block">Upload an Excel file (.xls, .xlsx)</span>
    </div>
    <!-- Submit button for file upload -->
    <button type="submit" name="importExcel" class="btn btn-primary btn-labeled">Import Excel<span class="btn-label btn-label-right"><i class="fa fa-upload"></i></span></button>
</form>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.col-md-12 -->
                                </div>
                    </div>
                </div>
                <!-- /.content-container -->
            </div>
            <!-- /.content-wrapper -->
        </div>
        <!-- /.main-wrapper -->
        <script src="js/jquery/jquery-2.2.4.min.js"></script>
        <script src="js/bootstrap/bootstrap.min.js"></script>
        <script src="js/pace/pace.min.js"></script>
        <script src="js/lobipanel/lobipanel.min.js"></script>
        <script src="js/iscroll/iscroll.js"></script>
        <script src="js/prism/prism.js"></script>
        <script src="js/select2/select2.min.js"></script>
        <script src="js/main.js"></script>
        <script>
            $(function($) {
                $(".js-states").select2();
                $(".js-states-limit").select2({
                    maximumSelectionLength: 2
                });
                $(".js-states-hide").select2({
                    minimumResultsForSearch: Infinity
                });
            });
        </script>
    </body>
</html>
