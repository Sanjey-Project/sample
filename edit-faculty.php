<?php
session_start();
//error_reporting(0);
include('includes/config_elasticsearch.php');
if(strlen($_SESSION['alogin'])=="")
    {   
    header("Location: login.php"); 
    }
    else{
if(isset($_POST['Update']))
{
$sid=intval($_GET['facultyid']);
$facultyname=$_POST['facultyname'];
$facultycode=$_POST['facultycode'];
$qualification=$_POST['qualification'];
$contact=$_POST['contact'];
// Get existing faculty data
$existingFaculty = $es->get(INDEX_FACULTY, $sid);
if($existingFaculty['success'] && isset($existingFaculty['data']['_source'])) {
    $existingData = $existingFaculty['data']['_source'];
    
    // Update document in Elasticsearch
    $updateDoc = [
        'id' => $sid,
        'facultyName' => $facultyname,
        'facultyCode' => $facultycode,
        'qualification' => $qualification,
        'contact' => intval($contact),
        'creationDate' => isset($existingData['creationDate']) ? $existingData['creationDate'] : date('Y-m-d H:i:s'),
        'updationDate' => date('Y-m-d H:i:s')
    ];
    
    $updateResult = $es->index(INDEX_FACULTY, $sid, $updateDoc);
    if($updateResult['success']) {
        $msg="Faculty Info updated successfully";
        header("Location: manage-faculty.php");
        exit;
    } else {
        $error="Failed to update faculty";
    }
} else {
    $error="Faculty not found";
}
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SMS Admin Update faculty </title>
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
                                    <h2 class="title">Update Faculty</h2>
                                
                                </div>
                                
                                <!-- /.col-md-6 text-right -->
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="dashboard.php"><i class="fa fa-home"></i> Home</a></li>
                                        <li> Faculty</li>
                                        <li class="active">Update Faculty</li>
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
                                                    <h5>Update Faculty</h5>
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

 <?php
$sid=intval($_GET['facultyid']);
// Get faculty data from Elasticsearch
$facultyResult = $es->get(INDEX_FACULTY, $sid);
$cnt=1;
if($facultyResult['success'] && isset($facultyResult['data']['_source']))
{
    $result = $facultyResult['data']['_source'];
    ?>                                               
                                                    <div class="form-group">
                                                        <label for="default" class="col-sm-2 control-label">Faculty Name</label>
                                                        <div class="col-sm-10">
 <input type="text" name="facultyname" value="<?php echo htmlentities($result['facultyName']);?>" class="form-control" id="default" placeholder="Faculty Name" required="required">
                                                        </div>
                                                    </div>
<div class="form-group">
                                                        <label for="default" class="col-sm-2 control-label">Faculty Code</label>
                                                        <div class="col-sm-10">
 <input type="text" name="facultycode" class="form-control" value="<?php echo htmlentities($result['facultyCode']);?>"  id="default" placeholder="faculty code" required="required">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="default" class="col-sm-2 control-label">Qualification</label>
                                                        <div class="col-sm-10">
 <input type="text" name="qualification" class="form-control" value="<?php echo htmlentities($result['qualification']);?>"  id="default" placeholder="qualification" required="required">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="default" class="col-sm-2 control-label">Contact</label>
                                                        <div class="col-sm-10">
 <input type="number" name="contact" class="form-control" value="<?php echo htmlentities($result['contact']);?>"  id="default" placeholder="contact" required="required">
                                                        </div>
                                                    </div>

                                                    <?php }} ?>

                                                    
                                                    <div class="form-group">
                                                        <div class="col-sm-offset-2 col-sm-10">
                                                            <button type="submit" name="Update" class="btn btn-primary">Update</button>
                                                        </div>
                                                    </div>
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
