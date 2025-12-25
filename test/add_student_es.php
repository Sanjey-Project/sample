<?php
/**
 * Add Student - Elasticsearch Version
 * Converted from MySQL to Elasticsearch
 * Original: add-students.php (simplified version)
 */
session_start();
error_reporting(0);
include('includes/config_elasticsearch.php');

if(strlen($_SESSION['alogin'])=="") {
    header("Location: login.php");
    exit;
}

$msg = "";
$error = "";

if(isset($_POST['submit'])) {
    $studentname = $_POST['fullanme'];
    $roolid = $_POST['rollid'];
    $studentemail = $_POST['emailid'];
    $gender = $_POST['gender'];
    $classid = $_POST['class'];
    $dob = $_POST['dob'];
    $batch = $_POST['batch'];
    
    // Get class name for the document
    $classQuery = [
        'query' => [
            'term' => ['id' => intval($classid)]
        ]
    ];
    $classResult = $es->search(INDEX_CLASSES, $classQuery);
    $className = '';
    $section = '';
    if ($classResult['success'] && isset($classResult['data']['hits']['hits'][0])) {
        $classData = $classResult['data']['hits']['hits'][0]['_source'];
        $className = $classData['className'];
        $section = $classData['section'];
    }
    
    // Generate unique ID (or use rollId as ID)
    $studentId = time() . rand(1000, 9999); // You can use auto-increment logic here
    
    // Prepare document for Elasticsearch
    $document = [
        'studentId' => intval($studentId),
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
    $result = $es->index(INDEX_STUDENTS, $studentId, $document);
    
    if ($result['success']) {
        $msg = "Student info added successfully";
    } else {
        $error = "Something went wrong. Please try again. Error: " . json_encode($result['error']);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Student - Elasticsearch</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        .errorWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #dd3d36;
        }
        .succWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #5cb85c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add Student (Elasticsearch)</h2>
        
        <?php if($error){ ?>
            <div class="errorWrap">
                <strong>ERROR</strong>: <?php echo htmlentities($error); ?>
            </div>
        <?php } ?>
        
        <?php if($msg){ ?>
            <div class="succWrap">
                <strong>SUCCESS</strong>: <?php echo htmlentities($msg); ?>
            </div>
        <?php } ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullanme" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Roll ID</label>
                <input type="text" name="rollid" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="emailid" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Gender</label>
                <select name="gender" class="form-control" required>
                    <option value="">Select</option>
                    <option value="MALE">Male</option>
                    <option value="FEMALE">Female</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Class</label>
                <select name="class" class="form-control" required>
                    <option value="">Select</option>
                    <?php
                    // Get classes from Elasticsearch
                    $classQuery = [
                        'query' => ['match_all' => []],
                        'sort' => [['className.sortable' => 'asc']],
                        'size' => 1000
                    ];
                    $classResult = $es->search(INDEX_CLASSES, $classQuery);
                    if ($classResult['success'] && isset($classResult['data']['hits']['hits'])) {
                        foreach ($classResult['data']['hits']['hits'] as $hit) {
                            $class = $hit['_source'];
                            echo "<option value='" . $class['id'] . "'>" . htmlentities($class['className'] . " - " . $class['section']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="text" name="dob" class="form-control" placeholder="dd-MM-yy" required>
            </div>
            
            <div class="form-group">
                <label>Batch/Passed Out Year</label>
                <input type="number" name="batch" class="form-control" required>
            </div>
            
            <button type="submit" name="submit" class="btn btn-primary">Add Student</button>
        </form>
    </div>
</body>
</html>

