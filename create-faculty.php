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
$facultyname=$_POST['facultyname'];
$facultycode=$_POST['facultycode'];
$qualification=$_POST['qualification'];
$contact=$_POST['contact']; 

// Get next available ID
$newId = 1;
if($es->indexExists(INDEX_FACULTY)) {
    $maxIdQuery = [
        'query' => ['match_all' => []],
        'sort' => [['id' => ['order' => 'desc']]],
        'size' => 1
    ];
    $maxIdResult = $es->search(INDEX_FACULTY, $maxIdQuery);
    if($maxIdResult['success'] && isset($maxIdResult['data']['hits']['hits'][0])) {
        $maxId = $maxIdResult['data']['hits']['hits'][0]['_source']['id'];
        $newId = intval($maxId) + 1;
    }
}

// Prepare document for Elasticsearch
$document = [
    'id' => $newId,
    'facultyName' => $facultyname,
    'facultyCode' => $facultycode,
    'qualification' => $qualification,
    'contact' => intval($contact),
    'creationDate' => date('Y-m-d H:i:s'),
    'updationDate' => date('Y-m-d H:i:s')
];

// Index the document
$result = $es->index(INDEX_FACULTY, $newId, $document);

if($result['success'])
{
$msg="Faculty Created successfully";
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
            $facultyname = $row[0];
            $facultycode = $row[1];
            $qualification = $row[2];
            $contact = $row[3];
            // Check if faculty already exists
            $facultyExists = false;
            $existingFacultyId = null;
            if($es->indexExists(INDEX_FACULTY)) {
                $checkQuery = [
                    'query' => [
                        'bool' => [
                            'must' => [
                                ['term' => ['facultyName.keyword' => $facultyname]],
                                ['term' => ['facultyCode' => $facultycode]]
                            ]
                        ]
                    ],
                    'size' => 1
                ];
                $checkResult = $es->search(INDEX_FACULTY, $checkQuery);
                if($checkResult['success']) {
                    $totalHits = isset($checkResult['data']['hits']['total']['value']) ? 
                                 $checkResult['data']['hits']['total']['value'] : 
                                 (isset($checkResult['data']['hits']['total']) ? intval($checkResult['data']['hits']['total']) : 0);
                    if($totalHits > 0 && isset($checkResult['data']['hits']['hits'][0])) {
                        $facultyExists = true;
                        $existingFacultyId = $checkResult['data']['hits']['hits'][0]['_source']['id'];
                    }
                }
            }
    
            if($facultyExists && $existingFacultyId) {
                // Record exists, update it
                $updateDoc = [
                    'id' => $existingFacultyId,
                    'facultyName' => $facultyname,
                    'facultyCode' => $facultycode,
                    'qualification' => $qualification,
                    'contact' => intval($contact),
                    'updationDate' => date('Y-m-d H:i:s')
                ];
                $updateResult = $es->index(INDEX_FACULTY, $existingFacultyId, $updateDoc);
                if($updateResult['success']) {
                    $msg = "Faculty updated successfully";
                }
            } else {
                // Get next available ID
                $newId = 1;
                if($es->indexExists(INDEX_FACULTY)) {
                    $maxIdQuery = [
                        'query' => ['match_all' => []],
                        'sort' => [['id' => ['order' => 'desc']]],
                        'size' => 1
                    ];
                    $maxIdResult = $es->search(INDEX_FACULTY, $maxIdQuery);
                    if($maxIdResult['success'] && isset($maxIdResult['data']['hits']['hits'][0])) {
                        $maxId = $maxIdResult['data']['hits']['hits'][0]['_source']['id'];
                        $newId = intval($maxId) + 1;
                    }
                }
                
                $document = [
                    'id' => $newId,
                    'facultyName' => $facultyname,
                    'facultyCode' => $facultycode,
                    'qualification' => $qualification,
                    'contact' => intval($contact),
                    'creationDate' => date('Y-m-d H:i:s'),
                    'updationDate' => date('Y-m-d H:i:s')
                ];
                
                $insertResult = $es->index(INDEX_FACULTY, $newId, $document);
                if ($insertResult['success']) {
                    $msg = "Faculty Imported successfully";
                } else {
                    $error = "Error importing faculty. Please check your data.";
                }
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
        <title>Faculty Creation </title>
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
                                    <h2 class="title">Faculty Creation</h2>
                                
                                </div>
                                
                                <!-- /.col-md-6 text-right -->
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li> Faculty</li>
                                        <li class="active">Create Faculty</li>
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
                                                    <h5>Create Faculty</h5>
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
                                                        <label for="default" class="col-sm-2 control-label">Faculty Name</label>
                                                        <div class="col-sm-10">
 <input type="text" name="facultyname" class="form-control" id="default" placeholder="Faculty Name" required="required">
                                                        </div>
                                                    </div>
<div class="form-group">
                                                        <label for="default" class="col-sm-2 control-label">Faculty Code</label>
                                                        <div class="col-sm-10">
 <input type="text" name="facultycode" class="form-control" id="default" placeholder="ID" required="required">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="default" class="col-sm-2 control-label">Qualification</label>
                                                        <div class="col-sm-10">
 <input type="text" name="qualification" class="form-control" id="default" placeholder="Qualification" required="required">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="default" class="col-sm-2 control-label">Contact</label>
                                                        <div class="col-sm-10">
 <input type="number" name="contact" class="form-control" id="default" placeholder="ph no" required="required">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <div class="col-sm-offset-2 col-sm-10">
                                                            <button type="submit" name="submit" class="btn btn-primary">Submit</button>
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
