<?php
/**
 * Elasticsearch INSERT Query Examples
 * Direct examples for inserting data from mj.sql
 */

include('includes/config_elasticsearch.php');

// ============================================
// 1. ADMINISTRATORS (admindata)
// ============================================
function insertAdmins() {
    global $es;
    
    $admins = [
        [
            'id' => 1,
            'fullName' => 'GokuSan',
            'userName' => 'Songoku',
            'email' => 'sanjeykannaa12@gmail.com',
            'phoneNumber' => 9629530722,
            'password' => md5('Sanjey12@'),
            'updationDate' => '2024-05-01 14:22:58'
        ],
        [
            'id' => 2,
            'fullName' => 'Sanjey Kannaa V',
            'userName' => 'SANJEYKANNAA',
            'email' => 'sanjeykannaa@psnacet.edu.in',
            'phoneNumber' => 8754980763,
            'password' => md5('Sanjey12@'),
            'updationDate' => '2024-05-01 14:24:42'
        ],
        [
            'id' => 3,
            'fullName' => 'Eren Yeager',
            'userName' => 'Attacktitan',
            'email' => 'eren@gmail.com',
            'phoneNumber' => 1234567890,
            'password' => md5('eren12@'),
            'updationDate' => '2024-05-01 14:26:44'
        ],
        [
            'id' => 5,
            'fullName' => 'Levi Ackerman',
            'userName' => 'levi',
            'email' => 'levi@gmail.com',
            'phoneNumber' => 9876543210,
            'password' => md5('levi12@'),
            'updationDate' => '2024-05-01 14:30:47'
        ]
    ];
    
    foreach ($admins as $admin) {
        $result = $es->index(INDEX_ADMINISTRATORS, $admin['id'], $admin);
        echo "Admin {$admin['id']}: " . ($result['success'] ? 'OK' : 'FAILED') . "\n";
    }
}

// ============================================
// 2. CLASSES (classdata)
// ============================================
function insertClasses() {
    global $es;
    
    $classes = [
        ['id' => 1, 'className' => 'CSE', 'classNameNumeric' => 1, 'section' => 'A', 'creationDate' => '2024-05-04 04:01:37', 'updationDate' => '2024-05-04 04:01:37'],
        ['id' => 2, 'className' => 'CSE', 'classNameNumeric' => 1, 'section' => 'B', 'creationDate' => '2024-05-04 04:03:04', 'updationDate' => '2024-05-04 04:03:04'],
        ['id' => 3, 'className' => 'CSE', 'classNameNumeric' => 1, 'section' => 'C', 'creationDate' => '2024-05-04 04:03:04', 'updationDate' => '2024-05-04 04:03:04'],
        ['id' => 4, 'className' => 'CSE', 'classNameNumeric' => 1, 'section' => 'D', 'creationDate' => '2024-05-04 04:03:04', 'updationDate' => '2024-05-04 04:03:04'],
        ['id' => 5, 'className' => 'CSE', 'classNameNumeric' => 2, 'section' => 'A', 'creationDate' => '2024-05-04 04:03:04', 'updationDate' => '2024-05-04 04:03:04'],
        ['id' => 6, 'className' => 'CSE', 'classNameNumeric' => 2, 'section' => 'B', 'creationDate' => '2024-05-04 04:03:04', 'updationDate' => '2024-05-04 04:03:04'],
        ['id' => 7, 'className' => 'CSE', 'classNameNumeric' => 2, 'section' => 'C', 'creationDate' => '2024-05-04 04:03:04', 'updationDate' => '2024-05-04 04:03:04'],
        ['id' => 8, 'className' => 'CSE', 'classNameNumeric' => 2, 'section' => 'D', 'creationDate' => '2024-05-04 04:03:04', 'updationDate' => '2024-05-04 04:03:04'],
        ['id' => 9, 'className' => 'CSE', 'classNameNumeric' => 3, 'section' => 'A', 'creationDate' => '2024-05-04 04:03:04', 'updationDate' => '2024-05-04 04:03:04'],
        ['id' => 10, 'className' => 'CSE', 'classNameNumeric' => 3, 'section' => 'B', 'creationDate' => '2024-05-04 04:03:04', 'updationDate' => '2024-05-04 04:03:04'],
        ['id' => 11, 'className' => 'CSE', 'classNameNumeric' => 3, 'section' => 'C', 'creationDate' => '2024-05-04 04:03:04', 'updationDate' => '2024-05-04 04:03:04'],
        ['id' => 12, 'className' => 'CSE', 'classNameNumeric' => 3, 'section' => 'D', 'creationDate' => '2024-05-04 04:03:04', 'updationDate' => '2024-05-04 04:03:04'],
        ['id' => 13, 'className' => 'CSE', 'classNameNumeric' => 4, 'section' => 'A', 'creationDate' => '2024-05-04 04:03:04', 'updationDate' => '2024-05-04 04:03:04'],
        ['id' => 14, 'className' => 'CSE', 'classNameNumeric' => 4, 'section' => 'B', 'creationDate' => '2024-05-04 04:03:04', 'updationDate' => '2024-05-04 04:03:04'],
        ['id' => 15, 'className' => 'CSE', 'classNameNumeric' => 4, 'section' => 'C', 'creationDate' => '2024-05-04 04:03:04', 'updationDate' => '2024-05-04 04:03:04'],
        ['id' => 16, 'className' => 'CSE', 'classNameNumeric' => 4, 'section' => 'D', 'creationDate' => '2024-05-04 04:03:04', 'updationDate' => '2024-05-04 04:03:04'],
        ['id' => 18, 'className' => 'CIVIL', 'classNameNumeric' => 1, 'section' => 'A', 'creationDate' => '2024-05-05 14:51:27', 'updationDate' => '2024-05-05 14:51:27']
    ];
    
    foreach ($classes as $class) {
        $result = $es->index(INDEX_CLASSES, $class['id'], $class);
    }
    echo "Inserted " . count($classes) . " classes\n";
}

// ============================================
// 3. DEPARTMENTS (departmentdata)
// ============================================
function insertDepartments() {
    global $es;
    
    $departments = [
        ['id' => 1, 'departmentName' => 'CSE', 'username' => 'CSEadmin', 'password' => md5('CSE@password')],
        ['id' => 3, 'departmentName' => 'ECE', 'username' => 'ECEadmin', 'password' => md5('ECE@password')],
        ['id' => 4, 'departmentName' => 'CIVIL', 'username' => 'CIVILadmin', 'password' => md5('CIVIL@password')],
        ['id' => 5, 'departmentName' => 'AIDS', 'username' => 'AIDSadmin', 'password' => md5('AIDS@password')]
    ];
    
    foreach ($departments as $dept) {
        $es->index(INDEX_DEPARTMENTS, $dept['id'], $dept);
    }
    echo "Inserted " . count($departments) . " departments\n";
}

// ============================================
// Example: Insert Single Student
// ============================================
function insertSingleStudent() {
    global $es;
    
    $student = [
        'id' => 1,
        'studentName' => 'EREN YEAGER',
        'rollId' => '900020104100',
        'studentEmail' => 'eren@gmail.com',
        'gender' => 'MALE',
        'dob' => '01-01-02',
        'classId' => 13,
        'passedOutYear' => 2020,
        'regDate' => '2024-05-04 07:38:07',
        'updationDate' => '2024-05-15 03:06:36'
    ];
    
    $result = $es->index(INDEX_STUDENTS, $student['id'], $student);
    return $result['success'];
}

// ============================================
// Example: Insert Single Faculty
// ============================================
function insertSingleFaculty() {
    global $es;
    
    $faculty = [
        'id' => 1,
        'facultyName' => 'NARUTO UZUMAKI',
        'facultyCode' => 'FC001',
        'qualification' => 'ASSOCIATE PROFESSOR',
        'contact' => 1000000000,
        'creationDate' => '2024-05-04 06:21:42',
        'updationDate' => '2024-05-04 06:23:29'
    ];
    
    $result = $es->index(INDEX_FACULTY, $faculty['id'], $faculty);
    return $result['success'];
}

// ============================================
// Example: Insert Single Result
// ============================================
function insertSingleResult() {
    global $es;
    
    $result = [
        'id' => 1,
        'studentId' => 1,
        'classId' => 13,
        'subjectId' => 55,
        'marks' => 'B',
        'grades' => 6,
        'postingDate' => '2024-05-04 12:06:35',
        'updationDate' => '2024-05-04 17:15:21'
    ];
    
    $esResult = $es->index(INDEX_RESULTS, $result['id'], $result);
    return $esResult['success'];
}

// ============================================
// Example: Bulk Insert
// ============================================
function bulkInsertStudents($students) {
    global $es;
    
    $operations = [];
    foreach ($students as $student) {
        $operations[] = [
            'action' => ['index' => ['_index' => INDEX_STUDENTS, '_id' => $student['id']]],
            'data' => $student
        ];
    }
    
    $result = $es->bulk($operations);
    return $result;
}

// Usage examples:
// insertAdmins();
// insertClasses();
// insertDepartments();
// insertSingleStudent();
// insertSingleFaculty();
// insertSingleResult();

echo "Elasticsearch INSERT examples loaded.\n";
echo "Uncomment function calls to run migrations.\n";

?>

