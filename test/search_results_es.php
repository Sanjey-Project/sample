<?php
/**
 * Search Results - Elasticsearch Version
 * Example of complex queries with aggregations
 */
include('includes/config_elasticsearch.php');

// Example 1: Get results for a specific student
function getStudentResults($studentId) {
    global $es;
    
    $query = [
        'query' => [
            'term' => ['studentId' => intval($studentId)]
        ],
        'sort' => [['postingDate' => 'desc']],
        'size' => 100
    ];
    
    return $es->search(INDEX_RESULTS, $query);
}

// Example 2: Get class performance summary with aggregations
function getClassPerformance($classId) {
    global $es;
    
    $query = [
        'query' => [
            'term' => ['classId' => intval($classId)]
        ],
        'aggs' => [
            'average_grade' => [
                'avg' => ['field' => 'grades']
            ],
            'grade_distribution' => [
                'terms' => [
                    'field' => 'marks',
                    'size' => 10
                ]
            ],
            'subject_stats' => [
                'terms' => [
                    'field' => 'subjectName',
                    'size' => 20
                ],
                'aggs' => [
                    'avg_grade' => [
                        'avg' => ['field' => 'grades']
                    ]
                ]
            ]
        ],
        'size' => 0
    ];
    
    return $es->search(INDEX_RESULTS, $query);
}

// Example 3: Full-text search across multiple fields
function searchStudents($searchTerm) {
    global $es;
    
    $query = [
        'query' => [
            'multi_match' => [
                'query' => $searchTerm,
                'fields' => [
                    'studentName^3',      // Boost student name
                    'rollId^2',           // Boost roll ID
                    'studentEmail'
                ],
                'type' => 'best_fields',
                'fuzziness' => 'AUTO'     // Fuzzy matching
            ]
        ],
        'highlight' => [
            'fields' => [
                'studentName' => [],
                'rollId' => []
            ]
        ],
        'size' => 20
    ];
    
    return $es->search(INDEX_STUDENT_SEARCH, $query);
}

// Example 4: Range query for academic year
function getResultsByYear($startYear, $endYear) {
    global $es;
    
    $query = [
        'query' => [
            'range' => [
                'academicYear' => [
                    'gte' => $startYear,
                    'lte' => $endYear
                ]
            ]
        ],
        'sort' => [['academicYear' => 'desc']],
        'size' => 1000
    ];
    
    return $es->search(INDEX_ANNUAL_RESULTS, $query);
}

// Example 5: Complex bool query with filters
function getTopPerformers($classId, $minCGPA = 8.0) {
    global $es;
    
    $query = [
        'query' => [
            'bool' => [
                'must' => [
                    ['term' => ['classId' => intval($classId)]]
                ],
                'filter' => [
                    ['range' => ['cgpa' => ['gte' => $minCGPA]]]
                ]
            ]
        ],
        'sort' => [
            ['cgpa' => 'desc']
        ],
        'size' => 10
    ];
    
    return $es->search(INDEX_STUDENT_PERFORMANCE, $query);
}

// Example usage
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    switch($action) {
        case 'student_results':
            if (isset($_GET['studentId'])) {
                $result = getStudentResults($_GET['studentId']);
                header('Content-Type: application/json');
                echo json_encode($result, JSON_PRETTY_PRINT);
            }
            break;
            
        case 'class_performance':
            if (isset($_GET['classId'])) {
                $result = getClassPerformance($_GET['classId']);
                header('Content-Type: application/json');
                echo json_encode($result, JSON_PRETTY_PRINT);
            }
            break;
            
        case 'search':
            if (isset($_GET['q'])) {
                $result = searchStudents($_GET['q']);
                header('Content-Type: application/json');
                echo json_encode($result, JSON_PRETTY_PRINT);
            }
            break;
            
        case 'top_performers':
            if (isset($_GET['classId'])) {
                $minCGPA = isset($_GET['minCGPA']) ? floatval($_GET['minCGPA']) : 8.0;
                $result = getTopPerformers($_GET['classId'], $minCGPA);
                header('Content-Type: application/json');
                echo json_encode($result, JSON_PRETTY_PRINT);
            }
            break;
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Elasticsearch Query Examples</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>Elasticsearch Query Examples</h2>
        
        <div class="panel panel-default">
            <div class="panel-heading">1. Get Student Results</div>
            <div class="panel-body">
                <form method="get">
                    <input type="hidden" name="action" value="student_results">
                    <input type="number" name="studentId" placeholder="Student ID" required>
                    <button type="submit" class="btn btn-primary">Get Results</button>
                </form>
            </div>
        </div>
        
        <div class="panel panel-default">
            <div class="panel-heading">2. Class Performance with Aggregations</div>
            <div class="panel-body">
                <form method="get">
                    <input type="hidden" name="action" value="class_performance">
                    <input type="number" name="classId" placeholder="Class ID" required>
                    <button type="submit" class="btn btn-primary">Get Performance</button>
                </form>
            </div>
        </div>
        
        <div class="panel panel-default">
            <div class="panel-heading">3. Full-Text Search</div>
            <div class="panel-body">
                <form method="get">
                    <input type="hidden" name="action" value="search">
                    <input type="text" name="q" placeholder="Search term" required>
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
        </div>
        
        <div class="panel panel-default">
            <div class="panel-heading">4. Top Performers</div>
            <div class="panel-body">
                <form method="get">
                    <input type="hidden" name="action" value="top_performers">
                    <input type="number" name="classId" placeholder="Class ID" required>
                    <input type="number" name="minCGPA" placeholder="Min CGPA" step="0.1" value="8.0">
                    <button type="submit" class="btn btn-primary">Get Top Performers</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

