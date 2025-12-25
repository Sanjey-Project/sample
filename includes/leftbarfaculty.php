<?php
session_start();
include("includes/config_elasticsearch.php");

if(isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    $facultyResult = $es->get(INDEX_FACULTY, $id);
    if($facultyResult['success'] && isset($facultyResult['data']['_source'])) {
        $facultyName = $facultyResult['data']['_source']['facultyName'];
    } else {
        $facultyName = "Unknown";
    }
}
?>
<div class="left-sidebar bg-black-300 box-shadow">
    <div class="sidebar-content">
        <div class="user-info closed">
            <img src="http://placehold.it/90/c2c2c2?text=User" alt="Profile Picture" class="img-circle profile-img">
            <h6 class="title"><?php echo isset($facultyName) ? $facultyName : "Unknown"; ?>!</h6>
                            </div>
                            <!-- /.user-info -->

                            <div class="sidebar-nav">
                                <ul class="side-nav color-gray">
                                    <li class="nav-header">
                                        <span class="">Main Category</span>
                                    </li>
                                    <li>
                                        <a href="dashboardfaculty.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span> </a>
                                     
                                    </li>

                                    <li class="nav-header">
                                        <span class="">Results</span>
                                    </li>
                                    <li class="has-children">
                                            <li><a href="studentwisefc.php"><i class="fa fa-bars"></i> <span>Students Wise</span></a></li>
                                            <li><a href="classwisefc.php"><i class="fa fa fa-server"></i> <span>Class Wise</span></a></li>
                                            <li><a href="subjectwisefc.php"><i class="fa fa fa-server"></i> <span>Subject Wise</span></a></li>
                                           
                                    </li>
                                    
                            </div>
                            <!-- /.sidebar-nav -->
                        </div>
                        <!-- /.sidebar-content -->
                    </div>