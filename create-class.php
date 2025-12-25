<?php
session_start();
error_reporting(0);
include('includes/config_elasticsearch.php');
require 'excelReader/excel_reader2.php';
require 'excelReader/SpreadsheetReader.php';
if(strlen($_SESSION['alogin'])=="")
    {   
    header("Location: login.php"); 
    }
    else{
if(isset($_POST['submit']))
{
$classname=$_POST['classname'];
$classnamenumeric=$_POST['classnamenumeric']; 
$section=$_POST['section'];
// Create department entry
$departmentName = $classname;
$username = $classname . "admin";
$password = $classname . "@password";

// Get next available department ID
$newDeptId = 1;
if($es->indexExists(INDEX_DEPARTMENTS)) {
    $maxIdQuery = [
        'query' => ['match_all' => []],
        'sort' => [['id' => ['order' => 'desc']]],
        'size' => 1
    ];
    $maxIdResult = $es->search(INDEX_DEPARTMENTS, $maxIdQuery);
    if($maxIdResult['success'] && isset($maxIdResult['data']['hits']['hits'][0])) {
        $maxId = $maxIdResult['data']['hits']['hits'][0]['_source']['id'];
        $newDeptId = intval($maxId) + 1;
    }
}

$deptDoc = [
    'id' => $newDeptId,
    'departmentName' => $departmentName,
    'username' => $username,
    'password' => $password,
    'creationDate' => date('Y-m-d H:i:s')
];
$es->index(INDEX_DEPARTMENTS, $newDeptId, $deptDoc);

// Check if class already exists
$classExists = false;
$existingClassId = null;
if($es->indexExists(INDEX_CLASSES)) {
    $checkQuery = [
        'query' => [
            'bool' => [
                'must' => [
                    ['term' => ['className' => $classname]],
                    ['term' => ['classNameNumeric' => intval($classnamenumeric)]],
                    ['term' => ['section' => $section]]
                ]
            ]
        ],
        'size' => 1
    ];
    $checkResult = $es->search(INDEX_CLASSES, $checkQuery);
    if($checkResult['success']) {
        $totalHits = isset($checkResult['data']['hits']['total']['value']) ? 
                     $checkResult['data']['hits']['total']['value'] : 
                     (isset($checkResult['data']['hits']['total']) ? intval($checkResult['data']['hits']['total']) : 0);
        if($totalHits > 0 && isset($checkResult['data']['hits']['hits'][0])) {
            $classExists = true;
            $existingClassId = $checkResult['data']['hits']['hits'][0]['_source']['id'];
        }
    }
}

if($classExists && $existingClassId) {
    // Record exists, update it
    $updateDoc = [
        'id' => $existingClassId,
        'className' => $classname,
        'classNameNumeric' => intval($classnamenumeric),
        'section' => $section,
        'updationDate' => date('Y-m-d H:i:s')
    ];
    $updateResult = $es->index(INDEX_CLASSES, $existingClassId, $updateDoc);
    if($updateResult['success']) {
        $msg = "Class Updated successfully";
    }
} else {
    // Get next available class ID
    $newId = 1;
    if($es->indexExists(INDEX_CLASSES)) {
        $maxIdQuery = [
            'query' => ['match_all' => []],
            'sort' => [['id' => ['order' => 'desc']]],
            'size' => 1
        ];
        $maxIdResult = $es->search(INDEX_CLASSES, $maxIdQuery);
        if($maxIdResult['success'] && isset($maxIdResult['data']['hits']['hits'][0])) {
            $maxId = $maxIdResult['data']['hits']['hits'][0]['_source']['id'];
            $newId = intval($maxId) + 1;
        }
    }
    
    $document = [
        'id' => $newId,
        'className' => $classname,
        'classNameNumeric' => intval($classnamenumeric),
        'section' => $section,
        'creationDate' => date('Y-m-d H:i:s'),
        'updationDate' => date('Y-m-d H:i:s')
    ];
    
    $result = $es->index(INDEX_CLASSES, $newId, $document);
    if($result['success']) {
        $msg="Class Created successfully";
    } else {
        $error="Something went wrong. Please try again";
    }
}
}
else if (isset($_POST['importExcel'])) {
    // Handle Excel file import
    $filename = $_FILES['excelFile']['name'];
    $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
    
    // Check file extension (only allow .xls and .xlsx)
    if (in_array($fileExtension, ['xls', 'xlsx','csv'])) {
        $targetDirectory = "includes/" . $filename;
        move_uploaded_file($_FILES['excelFile']['tmp_name'], $targetDirectory);

        $reader = new SpreadsheetReader($targetDirectory);
        foreach ($reader as $key => $row) {
            $classname = $row[0];
            $classnamenumeric = $row[1];
            $section = $row[2];
            // Create department entry
            $departmentName = $classname;
            $username = $classname . "admin";
            $password = $classname . "@password";
            
            // Get next available department ID
            $newDeptId = 1;
            if($es->indexExists(INDEX_DEPARTMENTS)) {
                $maxIdQuery = [
                    'query' => ['match_all' => []],
                    'sort' => [['id' => ['order' => 'desc']]],
                    'size' => 1
                ];
                $maxIdResult = $es->search(INDEX_DEPARTMENTS, $maxIdQuery);
                if($maxIdResult['success'] && isset($maxIdResult['data']['hits']['hits'][0])) {
                    $maxId = $maxIdResult['data']['hits']['hits'][0]['_source']['id'];
                    $newDeptId = intval($maxId) + 1;
                }
            }
            
            $deptDoc = [
                'id' => $newDeptId,
                'departmentName' => $departmentName,
                'username' => $username,
                'password' => $password,
                'creationDate' => date('Y-m-d H:i:s')
            ];
            $es->index(INDEX_DEPARTMENTS, $newDeptId, $deptDoc);
            
            // Check if class already exists
            $classExists = false;
            $existingClassId = null;
            if($es->indexExists(INDEX_CLASSES)) {
                $checkQuery = [
                    'query' => [
                        'bool' => [
                            'must' => [
                                ['term' => ['className' => $classname]],
                                ['term' => ['classNameNumeric' => intval($classnamenumeric)]],
                                ['term' => ['section' => $section]]
                            ]
                        ]
                    ],
                    'size' => 1
                ];
                $checkResult = $es->search(INDEX_CLASSES, $checkQuery);
                if($checkResult['success']) {
                    $totalHits = isset($checkResult['data']['hits']['total']['value']) ? 
                                 $checkResult['data']['hits']['total']['value'] : 
                                 (isset($checkResult['data']['hits']['total']) ? intval($checkResult['data']['hits']['total']) : 0);
                    if($totalHits > 0 && isset($checkResult['data']['hits']['hits'][0])) {
                        $classExists = true;
                        $existingClassId = $checkResult['data']['hits']['hits'][0]['_source']['id'];
                    }
                }
            }
    
            if($classExists && $existingClassId) {
                // Record exists, update it
                $updateDoc = [
                    'id' => $existingClassId,
                    'className' => $classname,
                    'classNameNumeric' => intval($classnamenumeric),
                    'section' => $section,
                    'updationDate' => date('Y-m-d H:i:s')
                ];
                $updateResult = $es->index(INDEX_CLASSES, $existingClassId, $updateDoc);
                if($updateResult['success']) {
                    $msg = "Class updated successfully";
                }
            } else {
                // Get next available class ID
                $newId = 1;
                if($es->indexExists(INDEX_CLASSES)) {
                    $maxIdQuery = [
                        'query' => ['match_all' => []],
                        'sort' => [['id' => ['order' => 'desc']]],
                        'size' => 1
                    ];
                    $maxIdResult = $es->search(INDEX_CLASSES, $maxIdQuery);
                    if($maxIdResult['success'] && isset($maxIdResult['data']['hits']['hits'][0])) {
                        $maxId = $maxIdResult['data']['hits']['hits'][0]['_source']['id'];
                        $newId = intval($maxId) + 1;
                    }
                }
                
                $document = [
                    'id' => $newId,
                    'className' => $classname,
                    'classNameNumeric' => intval($classnamenumeric),
                    'section' => $section,
                    'creationDate' => date('Y-m-d H:i:s'),
                    'updationDate' => date('Y-m-d H:i:s')
                ];
                
                $insertResult = $es->index(INDEX_CLASSES, $newId, $document);
                if ($insertResult['success']) {
                    $msg = "Class Imported successfully";
                } else {
                    $error = "Error importing class. Please check your data.";
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
        <title>Create Class</title>
        <link rel="stylesheet" href="css/bootstrap.css" media="screen" >
        <link rel="stylesheet" href="css/font-awesome.min.css" media="screen" >
        <link rel="stylesheet" href="css/animate-css/animate.min.css" media="screen" >
        <link rel="stylesheet" href="css/lobipanel/lobipanel.min.css" media="screen" >
        <link rel="stylesheet" href="css/prism/prism.css" media="screen" > <!-- USED FOR DEMO HELP - YOU CAN REMOVE IT -->
        <link rel="stylesheet" href="css/main.css" media="screen" >
        <script src="js/modernizr/modernizr.min.js"></script>
         <style>
        .errorWrap {
    padding: 10px;
    margin: 0 0 20px 0;
    background: #fff;
    border-left: 4px solid #dd3d36;
    -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
}
.succWrap{
    padding: 10px;
    margin: 0 0 20px 0;
    background: #fff;
    border-left: 4px solid #5cb85c;
    -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
}
        </style>
    </head>
    <body class="top-navbar-fixed">
        <div class="main-wrapper">

            <!-- ========== TOP NAVBAR ========== -->
            <?php include('includes/topbar.php');?>   
          <!-----End Top bar>
            <--- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
            <div class="content-wrapper">
                <div class="content-container">

<!-- ========== LEFT SIDEBAR ========== -->
<?php include('includes/leftbar.php');?>                   
 <!-- /.left-sidebar -->

                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-6">
                                    <h2 class="title">Create Class</h2>
                                </div>
                                
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
            							<li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
            							<li><a href="#">Classes</a></li>
            							<li class="active">Create Class</li>
            						</ul>
                                </div>
                               
                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->

                        <section class="section">
                            <div class="container-fluid">

                             

                              

                                <div class="row">
                                    <div class="col-md-8 col-md-offset-2">
                                        <div class="panel">
                                            <div class="panel-heading">
                                                <div class="panel-title">
                                                    <h5>Create Class</h5>
                                                </div>
                                            </div>
           <?php if($msg){?>
<div class="alert alert-success left-icon-alert" role="alert">
 <strong>Well done!</strong><?php echo htmlentities($msg); ?>
 </div><?php } 
else if($error){?>
    <div class="alert alert-danger left-icon-alert" role="alert">
                                            <strong>Oh snap!</strong> <?php echo htmlentities($error); ?>
                                        </div>
                                        <?php } ?>
  
                                            <div class="panel-body">

                                                <form method="post">
                                                    <div class="form-group has-success">
                                                        <label for="success" class="control-label">Department Name</label>
                                                		<div class="">
                                                			<input type="text" name="classname" class="form-control" required="required" id="success">
                                                            <span class="help-block">Eg- CSE,ECE,EEE etc</span>
                                                		</div>
                                                	</div>
                                                       <div class="form-group has-success">
                                                        <label for="success" class="control-label">Year Name</label>
                                                        <div class="">
                                                            <input type="number" name="classnamenumeric" required="required" class="form-control" id="success">
                                                            <span class="help-block">Eg- 1,2,3,4 etc</span>
                                                        </div>
                                                    </div>
                                                     <div class="form-group has-success">
                                                        <label for="success" class="control-label">Section</label>
                                                        <div class="">
                                                            <input type="text" name="section" class="form-control" required="required" id="success">
                                                            <span class="help-block">Eg- A,B,C etc</span>
                                                        </div>
                                                    </div>
  <div class="form-group has-success">

                                                        <div class="">
                                                           <button type="submit" name="submit" class="btn btn-success btn-labeled">Submit<span class="btn-label btn-label-right"><i class="fa fa-check"></i></span></button>
                                                    </div>                                                
                                                </form>
                                                <form method="post" enctype="multipart/form-data">
    <!-- File upload field -->
    <div class="form-group has-success">
        <label for="excelFile" class="control-label">Multiple Uploads ? Click Below</label>
        <input type="file" name="excelFile" id="excelFile" class="form-control" accept=".xls,.xlsx">
        <span class="help-block">Upload an Excel file (.xls, .xlsx).Column Format(dept,yr,sec)</span>
    </div>
    <!-- Submit button for file upload -->
    <button type="submit" name="importExcel" class="btn btn-primary btn-labeled">Import Excel<span class="btn-label btn-label-right"><i class="fa fa-upload"></i></span></button>
</form>
                                              
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.col-md-8 col-md-offset-2 -->
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

        <!-- ========== THEME JS ========== -->
        <script src="js/main.js"></script>



        <!-- ========== ADD custom.js FILE BELOW WITH YOUR CHANGES ========== -->
    </body>
</html>
<?php  } ?>
