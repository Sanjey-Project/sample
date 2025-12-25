<?php
session_start();
error_reporting(0);
require 'excelReader/excel_reader2.php';
require 'excelReader/SpreadsheetReader.php';
include('includes/config_elasticsearch.php');
if(strlen($_SESSION['alogin'])=="")
    {   
    header("Location: login.php"); 
    }
    else{
if(isset($_POST['submit']))
{
$studentname=$_POST['fullanme'];
$roolid=$_POST['rollid']; 
$studentemail=$_POST['emailid']; 
$gender=$_POST['gender']; 
$classid=$_POST['class']; 
$dob=$_POST['dob'];
$batch =$_POST['batch']; 

// Get class name and section from Elasticsearch
$className = '';
$section = '';
if($es->indexExists(INDEX_CLASSES)) {
    $classQuery = [
        'query' => [
            'term' => ['id' => intval($classid)]
        ],
        'size' => 1
    ];
    $classResult = $es->search(INDEX_CLASSES, $classQuery);
    if($classResult['success'] && isset($classResult['data']['hits']['hits'][0])) {
        $classData = $classResult['data']['hits']['hits'][0]['_source'];
        $className = $classData['className'];
        $section = $classData['section'];
    }
}

// Get next available student ID
$newStudentId = 1;
if($es->indexExists(INDEX_STUDENTS)) {
    $maxIdQuery = [
        'query' => ['match_all' => []],
        'sort' => [['studentId' => ['order' => 'desc']]],
        'size' => 1
    ];
    $maxIdResult = $es->search(INDEX_STUDENTS, $maxIdQuery);
    if($maxIdResult['success'] && isset($maxIdResult['data']['hits']['hits'][0])) {
        $maxId = $maxIdResult['data']['hits']['hits'][0]['_source']['studentId'];
        $newStudentId = intval($maxId) + 1;
    }
}

// Prepare document for Elasticsearch
$document = [
    'studentId' => $newStudentId,
    'studentName' => $studentname,
    'rollId' => $roolid,
    'studentEmail' => $studentemail,
    'gender' => $gender,
    'dob' => $dob,
    'classId' => intval($classid),
    'className' => $className,
    'section' => $section,
    'passedOutYear' => intval($batch),
    'regDate' => date('Y-m-d H:i:s'),
    'updationDate' => date('Y-m-d H:i:s')
];

// Index the document (INSERT equivalent)
$result = $es->index(INDEX_STUDENTS, $newStudentId, $document);

if($result['success'])
{
$msg="Student info added successfully";
}
else 
{
$error="Something went wrong. Please try again";
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
        foreach ($reader as $key => $row) {
            $studentname = $row[0];
            $rollid=$row[1];
            $studentemail=$row[2];
            $gender=$row[3];
            $dob=$row[4];
            $dept=$row[5];
            $year=$row[6];
            $section=$row[7];
            $batch = $row[8];
            // Get class ID from Elasticsearch
            $classid = null;
            if($es->indexExists(INDEX_CLASSES)) {
                $classQuery = [
                    'query' => [
                        'bool' => [
                            'must' => [
                                ['term' => ['className' => $dept]],
                                ['term' => ['classNameNumeric' => intval($year)]],
                                ['term' => ['section' => $section]]
                            ]
                        ]
                    ],
                    'size' => 1
                ];
                $classResult = $es->search(INDEX_CLASSES, $classQuery);
                if($classResult['success'] && isset($classResult['data']['hits']['hits'][0])) {
                    $classid = $classResult['data']['hits']['hits'][0]['_source']['id'];
                }
            }

            // Check if student already exists
            $studentExists = false;
            $existingStudentId = null;
            if($es->indexExists(INDEX_STUDENTS) && $classid) {
                $checkQuery = [
                    'query' => [
                        'bool' => [
                            'must' => [
                                ['term' => ['studentName.keyword' => $studentname]],
                                ['term' => ['rollId' => $rollid]]
                            ]
                        ]
                    ],
                    'size' => 1
                ];
                $checkResult = $es->search(INDEX_STUDENTS, $checkQuery);
                if($checkResult['success']) {
                    $totalHits = isset($checkResult['data']['hits']['total']['value']) ? 
                                 $checkResult['data']['hits']['total']['value'] : 
                                 (isset($checkResult['data']['hits']['total']) ? intval($checkResult['data']['hits']['total']) : 0);
                    if($totalHits > 0 && isset($checkResult['data']['hits']['hits'][0])) {
                        $studentExists = true;
                        $existingStudentId = $checkResult['data']['hits']['hits'][0]['_source']['studentId'];
                    }
                }
            }
    
            if($studentExists && $existingStudentId) {
                // Record exists, update it
                $updateDoc = [
                    'studentName' => $studentname,
                    'rollId' => $rollid,
                    'studentEmail' => $studentemail,
                    'gender' => $gender,
                    'dob' => $dob,
                    'classId' => intval($classid),
                    'className' => $dept,
                    'section' => $section,
                    'passedOutYear' => intval($batch),
                    'updationDate' => date('Y-m-d H:i:s')
                ];
                $updateResult = $es->index(INDEX_STUDENTS, $existingStudentId, $updateDoc);
                if($updateResult['success']) {
                    $msg = "Student updated successfully";
                }
            } else if($classid) {
                // Get next available student ID
                $newStudentId = 1;
                if($es->indexExists(INDEX_STUDENTS)) {
                    $maxIdQuery = [
                        'query' => ['match_all' => []],
                        'sort' => [['studentId' => ['order' => 'desc']]],
                        'size' => 1
                    ];
                    $maxIdResult = $es->search(INDEX_STUDENTS, $maxIdQuery);
                    if($maxIdResult['success'] && isset($maxIdResult['data']['hits']['hits'][0])) {
                        $maxId = $maxIdResult['data']['hits']['hits'][0]['_source']['studentId'];
                        $newStudentId = intval($maxId) + 1;
                    }
                }
                
                $document = [
                    'studentId' => $newStudentId,
                    'studentName' => $studentname,
                    'rollId' => $rollid,
                    'studentEmail' => $studentemail,
                    'gender' => $gender,
                    'dob' => $dob,
                    'classId' => intval($classid),
                    'className' => $dept,
                    'section' => $section,
                    'passedOutYear' => intval($batch),
                    'regDate' => date('Y-m-d H:i:s'),
                    'updationDate' => date('Y-m-d H:i:s')
                ];
                
                $insertResult = $es->index(INDEX_STUDENTS, $newStudentId, $document);
                if ($insertResult['success']) {
                    $msg = "Student Imported successfully";
                } else {
                    $error = "Error importing student. Please check your data.";
                }
            } else {
                $error = "Class not found. Please check your data.";
            }
}
} else
{
    $error = "Invalid file format. Please upload an Excel file (.xls or .xlsx)";
}
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SMS Admin| Student Admission< </title>
        <link rel="stylesheet" href="css/bootstrap.min.css" media="screen" >
        <link rel="stylesheet" href="css/font-awesome.min.css" media="screen" >
        <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen" >
        <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen" >
        <link rel="stylesheet" href="css/prism/prism.css" media="screen" >
        <link rel="stylesheet" href="css/select2/select2.min.css" >
        <link rel="stylesheet" href="css/main.css" media="screen" >
        <script src="js/modernizr/modernizr.min.js"></script>
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
                                    <h2 class="title">Student Admission</h2>
                                
                                </div>
                                
                                <!-- /.col-md-6 text-right -->
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                
                                        <li class="active">Student Admission</li>
                                    </ul>
                                </div>
                             
                            </div>
                            <!-- /.row -->
                        </div>
                        <div class="container-fluid">
                           
                        <div class="row">
                                    <div class="col-md-12">
                                        <div class="panel">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                    <h5>Fill the Student info</h5>
                                                </div>
                                            </div>
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
<label for="default" class="col-sm-2 control-label">Full Name</label>
<div class="col-sm-10">
<input type="text" name="fullanme" class="form-control" id="fullanme" required="required" autocomplete="off">
</div>
</div>

<div class="form-group">
<label for="default" class="col-sm-2 control-label">Register No</label>
<div class="col-sm-10">
<input type="text" name="rollid" class="form-control" id="rollid"  required="required" autocomplete="off">
</div>
</div>

<div class="form-group">
<label for="default" class="col-sm-2 control-label">Email id</label>
<div class="col-sm-10">
<input type="email" name="emailid" class="form-control" id="email" required="required" autocomplete="off">
</div>
</div>



<div class="form-group">
<label for="default" class="col-sm-2 control-label">Gender</label>
<div class="col-sm-10">
<input type="radio" name="gender" value="Male" required="required" checked="">Male <input type="radio" name="gender" value="Female" required="required">Female <input type="radio" name="gender" value="Other" required="required">Other
</div>
</div>










                                                    <div class="form-group">
                                                        <label for="default" class="col-sm-2 control-label">Class</label>
                                                        <div class="col-sm-10">
 <select name="class" class="form-control" id="default" required="required">
<option value="">Select Class</option>
<?php 
if($es->indexExists(INDEX_CLASSES)) {
    $classQuery = [
        'query' => ['match_all' => []],
        'sort' => [['className.sortable' => 'asc'], ['classNameNumeric' => 'asc'], ['section' => 'asc']],
        'size' => 1000
    ];
    $classResult = $es->search(INDEX_CLASSES, $classQuery);
    if($classResult['success'] && isset($classResult['data']['hits']['hits'])) {
        foreach($classResult['data']['hits']['hits'] as $hit) {
            $result = $hit['_source'];
            ?>
            <option value="<?php echo htmlentities($result['id']); ?>"><?php echo htmlentities($result['className']); ?>&nbsp; <?php echo htmlentities($result['classNameNumeric']); ?>&nbsp;Section-<?php echo htmlentities($result['section']); ?></option>
            <?php 
        }
    }
}
?>
 </select>
                                                        </div>
                                                    </div>
<div class="form-group">
                                                        <label for="date" class="col-sm-2 control-label">DOB</label>
                                                        <div class="col-sm-10">
                                                            <input type="date"  name="dob" class="form-control" id="date">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="number" class="col-sm-2 control-label">Batch</label>
                                                        <div class="col-sm-10">
                                                            <input type="number"  name="batch" class="form-control" id="number">
                                                        </div>
                                                    </div>

                                                    
                                                    <div class="form-group">
                                                        <div class="col-sm-offset-2 col-sm-10">
                                                            <button type="submit" name="submit" class="btn btn-primary">Add</button>
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
<?PHP } ?>
