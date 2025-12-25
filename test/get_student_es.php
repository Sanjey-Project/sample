<?php
/**
 * Get Student - Elasticsearch Version
 * Converted from MySQL to Elasticsearch
 * Original: get_student.php
 */
include('includes/config_elasticsearch.php');

// Get students by class ID (replaces MySQL query)
if(!empty($_POST["classid"])) {
    $cid = intval($_POST['classid']);
    
    if(!is_numeric($cid)) {
        echo htmlentities("invalid Class");
        exit;
    }
    
    // Elasticsearch query to get students by classId
    $query = [
        'query' => [
            'term' => [
                'classId' => $cid
            ]
        ],
        'sort' => [
            'studentName.sortable' => 'asc'
        ],
        'size' => 1000
    ];
    
    $result = $es->search(INDEX_STUDENTS, $query);
    
    if ($result['success']) {
        ?><option value="">Select Student</option><?php
        if (isset($result['data']['hits']['hits'])) {
            foreach ($result['data']['hits']['hits'] as $hit) {
                $student = $hit['_source'];
                ?>
                <option value="<?php echo htmlentities($student['studentId']); ?>">
                    <?php echo htmlentities($student['studentName']); ?>
                </option>
                <?php
            }
        }
    } else {
        echo "<option value=''>Error loading students</option>";
    }
}

// Get subjects by class ID
if(!empty($_POST["classid1"])) {
    $cid1 = intval($_POST['classid1']);
    
    if(!is_numeric($cid1)) {
        echo htmlentities("invalid Class");
        exit;
    }
    
    // Elasticsearch query to get subjects for a class
    $query = [
        'query' => [
            'bool' => [
                'must' => [
                    ['term' => ['classId' => $cid1]],
                    ['term' => ['status' => ['value' => 0, 'boost' => 1.0]]]
                ]
            ]
        ],
        'sort' => [
            'subjectName.sortable' => 'asc'
        ],
        'size' => 1000
    ];
    
    $result = $es->search(INDEX_CURRICULUM_MAPPINGS, $query);
    
    if ($result['success'] && isset($result['data']['hits']['hits'])) {
        foreach ($result['data']['hits']['hits'] as $hit) {
            $subject = $hit['_source'];
            ?>
            <p>
                <?php echo htmlentities($subject['subjectName']); ?>
                <input type="text" name="marks[]" value="" class="form-control" required="" 
                       placeholder="Enter the Grade" autocomplete="off">
            </p>
            <?php
        }
    }
}

// Check if result already exists
if(!empty($_POST["studclass"])) {
    $id = $_POST['studclass'];
    $dta = explode("$", $id);
    $classId = $dta[0];
    $studentId = $dta[1];
    
    // Elasticsearch query to check if result exists
    $query = [
        'query' => [
            'bool' => [
                'must' => [
                    ['term' => ['studentId' => intval($studentId)]],
                    ['term' => ['classId' => intval($classId)]]
                ]
            ]
        ],
        'size' => 1
    ];
    
    $result = $es->search(INDEX_RESULTS, $query);
    
    if ($result['success'] && isset($result['data']['hits']['total']['value']) && $result['data']['hits']['total']['value'] > 0) {
        ?>
        <p>
            <span style='color:red'>Result Already Declared.</span>
            <script>$('#submit').prop('disabled',true);</script>
        </p>
        <?php
    }
}
?>

