<?php
session_start();
include('includes/config_elasticsearch.php');

if(isset($_SESSION['StudentId'])) {
    $id = $_SESSION['StudentId'];
    $studentResult = $es->get(INDEX_STUDENTS, $id);
    if($studentResult['success'] && isset($studentResult['data']['_source'])) {
        $studentname = $studentResult['data']['_source']['studentName'];
    } else {
        $studentname = "Unknown";
    }
}?>
<div class="left-sidebar bg-black-300 box-shadow ">
                        <div class="sidebar-content">
                            <div class="user-info closed">
                                <img src="http://placehold.it/90/c2c2c2?text=User" alt="John Doe" class="img-circle profile-img">
                                <h6 class="title"><?php echo isset($studentname) ? $studentname : "Unknown"; ?>!</h6>
                            </div>
                            <!-- /.user-info -->

                            <div class="sidebar-nav">
                                <ul class="side-nav color-gray">
                                    <li class="nav-header">
                                        <span class="">Main Category</span>
                                    </li>
                                    <li>
                                        <a href="dashboardstudent.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span> </a>
                                     
                                    </li>

                                    <li class="nav-header">
                                        <span class="">Results</span>
                                    </li>
                                    <li class="has-children">
                                    <li><a href="semester.php"><i class="fa fa-bars"></i> <span>Semester Result</span></a></li>
                                            <li><a href="mark.php"><i class="fa fa fa-server"></i> <span>Mark</span></a></li>
                                    </li>
                            </div>
                            <!-- /.sidebar-nav -->
                        </div>
                        <!-- /.sidebar-content -->
                    </div>