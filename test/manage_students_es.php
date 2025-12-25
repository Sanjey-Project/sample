<?php
/**
 * Manage Students - Elasticsearch Version
 * Converted from MySQL to Elasticsearch
 * Original: manage-students.php (simplified version)
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

// Handle delete
if(isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $result = $es->delete(INDEX_STUDENTS, $id);
    
    if ($result['success']) {
        $msg = "Student deleted successfully";
    } else {
        $error = "Error deleting student";
    }
}

// Search query
$searchQuery = [
    'query' => ['match_all' => []],
    'sort' => [['studentName.sortable' => 'asc']],
    'size' => 1000
];

// If search term provided
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $searchQuery = [
        'query' => [
            'multi_match' => [
                'query' => $searchTerm,
                'fields' => ['studentName^2', 'rollId', 'studentEmail'],
                'type' => 'best_fields'
            ]
        ],
        'sort' => [['studentName.sortable' => 'asc']],
        'size' => 1000
    ];
}

$result = $es->search(INDEX_STUDENTS, $searchQuery);
$students = [];

if ($result['success'] && isset($result['data']['hits']['hits'])) {
    foreach ($result['data']['hits']['hits'] as $hit) {
        $students[] = $hit['_source'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Students - Elasticsearch</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../js/DataTables/datatables.min.css"/>
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
        <h2>Manage Students (Elasticsearch)</h2>
        
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
        
        <!-- Search Form -->
        <form method="get" style="margin-bottom: 20px;">
            <div class="form-group">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search by name, roll ID, or email" 
                       value="<?php echo isset($_GET['search']) ? htmlentities($_GET['search']) : ''; ?>">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="manage_students_es.php" class="btn btn-default">Reset</a>
        </form>
        
        <!-- Students Table -->
        <table id="studentsTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Roll ID</th>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Gender</th>
                    <th>Class</th>
                    <th>Batch</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $cnt = 1;
                foreach($students as $student) {
                    ?>
                    <tr>
                        <td><?php echo $cnt; ?></td>
                        <td><?php echo htmlentities($student['rollId']); ?></td>
                        <td><?php echo htmlentities($student['studentName']); ?></td>
                        <td><?php echo htmlentities($student['studentEmail']); ?></td>
                        <td><?php echo htmlentities($student['gender']); ?></td>
                        <td><?php echo htmlentities($student['className'] . ' - ' . $student['section']); ?></td>
                        <td><?php echo htmlentities($student['passedOutYear']); ?></td>
                        <td>
                            <a href="edit_student_es.php?id=<?php echo $student['studentId']; ?>" 
                               class="btn btn-sm btn-primary">Edit</a>
                            <a href="manage_students_es.php?del=<?php echo $student['studentId']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Do you want to delete?');">Delete</a>
                        </td>
                    </tr>
                    <?php
                    $cnt++;
                }
                ?>
            </tbody>
        </table>
        
        <p><strong>Total Students:</strong> <?php echo count($students); ?></p>
    </div>
    
    <script src="../js/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="../js/DataTables/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#studentsTable').DataTable();
        });
    </script>
</body>
</html>

