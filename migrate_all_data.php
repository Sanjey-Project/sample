<?php
/**
 * Migrate All Data from MySQL SQL Dump to Elasticsearch
 * Converts all INSERT statements from mj.sql to Elasticsearch operations
 */

include('includes/config_elasticsearch.php');

// Table to Index mapping
$tableToIndex = [
    'admindata' => INDEX_ADMINISTRATORS,
    'classdata' => INDEX_CLASSES,
    'departmentdata' => INDEX_DEPARTMENTS,
    'facultycombinationdata' => INDEX_FACULTY_ASSIGNMENTS,
    'facultydata' => INDEX_FACULTY,
    'resultdata' => INDEX_RESULTS,
    'studentdata' => INDEX_STUDENTS,
    'studentlogin' => INDEX_STUDENTS, // Note: studentlogin is merged with studentdata in ES
    'subjectcombinationdata' => INDEX_CURRICULUM_MAPPINGS,
    'subjectdata' => INDEX_SUBJECTS
];

// Field name mappings (MySQL -> Elasticsearch)
$fieldMappings = [
    'admindata' => [
        'id' => 'id',
        'FullName' => 'fullName',
        'UserName' => 'userName',
        'email' => 'email',
        'Phno' => 'phoneNumber',
        'Password' => 'password',
        'UpdationDate' => 'updationDate'
    ],
    'classdata' => [
        'id' => 'id',
        'ClassName' => 'className',
        'ClassNameNumeric' => 'classNameNumeric',
        'Section' => 'section',
        'CreationDate' => 'creationDate',
        'UpdationDate' => 'updationDate'
    ],
    'departmentdata' => [
        'id' => 'id',
        'DepartmentName' => 'departmentName',
        'Username' => 'username',
        'Password' => 'password'
    ],
    'facultycombinationdata' => [
        'id' => 'id',
        'FacultyId' => 'facultyId',
        'ClassId' => 'classId',
        'SubjectId' => 'subjectId',
        'status' => 'status',
        'CreationDate' => 'creationDate',
        'UpdationDate' => 'updationDate'
    ],
    'facultydata' => [
        'id' => 'id',
        'FacultyName' => 'facultyName',
        'FacultyCode' => 'facultyCode',
        'Qualification' => 'qualification',
        'contact' => 'contact',
        'Creationdate' => 'creationDate',
        'UpdationDate' => 'updationDate'
    ],
    'resultdata' => [
        'id' => 'id',
        'StudentId' => 'studentId',
        'ClassId' => 'classId',
        'SubjectId' => 'subjectId',
        'marks' => 'marks',
        'Grades' => 'grades',
        'PostingDate' => 'postingDate',
        'UpdationDate' => 'updationDate'
    ],
    'studentdata' => [
        'StudentId' => 'id',
        'StudentName' => 'studentName',
        'RollId' => 'rollId',
        'StudentEmail' => 'studentEmail',
        'Gender' => 'gender',
        'DOB' => 'dob',
        'ClassId' => 'classId',
        'passedoutyear' => 'passedOutYear',
        'RegDate' => 'regDate',
        'UpdationDate' => 'updationDate'
    ],
    'subjectcombinationdata' => [
        'id' => 'id',
        'ClassId' => 'classId',
        'SubjectId' => 'subjectId',
        'status' => 'status',
        'CreationDate' => 'creationDate',
        'Updationdate' => 'updationDate'
    ],
    'subjectdata' => [
        'id' => 'id',
        'SubjectName' => 'subjectName',
        'SubjectCode' => 'subjectCode',
        'credit' => 'credit',
        'semester' => 'semester',
        'Creationdate' => 'creationDate',
        'UpdationDate' => 'updationDate'
    ],
    'studentlogin' => [
        'Id' => 'id',
        'Password' => 'password'
        // Note: studentlogin table is typically merged with studentdata
        // If you have studentlogin data, it should be merged with existing student records
    ]
];

/**
 * Parse SQL INSERT statement and convert to Elasticsearch document
 */
function parseInsertStatement($sql, $tableName, $fieldMapping) {
    // Extract VALUES part
    if (preg_match('/VALUES\s+(.+);?$/is', $sql, $matches)) {
        $valuesPart = trim($matches[1]);
        
        // Parse multiple rows
        $rows = [];
        $currentRow = '';
        $parenDepth = 0;
        $inString = false;
        $escapeNext = false;
        
        for ($i = 0; $i < strlen($valuesPart); $i++) {
            $char = $valuesPart[$i];
            
            if ($escapeNext) {
                $currentRow .= $char;
                $escapeNext = false;
                continue;
            }
            
            if ($char === '\\') {
                $escapeNext = true;
                $currentRow .= $char;
                continue;
            }
            
            if ($char === "'" && !$escapeNext) {
                $inString = !$inString;
                $currentRow .= $char;
                continue;
            }
            
            if (!$inString) {
                if ($char === '(') {
                    $parenDepth++;
                    if ($parenDepth === 1) {
                        $currentRow = '';
                        continue;
                    }
                } elseif ($char === ')') {
                    $parenDepth--;
                    if ($parenDepth === 0) {
                        $rows[] = $currentRow;
                        $currentRow = '';
                        continue;
                    }
                } elseif ($char === ',' && $parenDepth === 0) {
                    continue;
                }
            }
            
            $currentRow .= $char;
        }
        
        // Parse each row
        $documents = [];
        foreach ($rows as $row) {
            // Extract field values
            preg_match_all("/'([^'\\\\]*(?:\\\\.[^'\\\\]*)*)'|(\d+)|(NULL)/i", $row, $matches);
            $values = array_filter($matches[0], function($v) {
                return $v !== '';
            });
            $values = array_values($values);
            
            // Map to Elasticsearch document
            $doc = [];
            $fieldIndex = 0;
            foreach ($fieldMapping as $mysqlField => $esField) {
                if (isset($values[$fieldIndex])) {
                    $value = trim($values[$fieldIndex], "'");
                    if ($value === 'NULL' || $value === '') {
                        continue;
                    }
                    
                    // Type conversion
                    if (in_array($mysqlField, ['id', 'StudentId', 'ClassId', 'SubjectId', 'FacultyId', 'ClassNameNumeric', 'credit', 'semester', 'passedoutyear', 'status', 'Grades'])) {
                        $doc[$esField] = intval($value);
                    } elseif (in_array($mysqlField, ['Phno', 'contact'])) {
                        $doc[$esField] = intval($value);
                    } elseif ($mysqlField === 'DOB' && $esField === 'dob') {
                        // Convert DOB from MM-dd-yy to dd-MM-yy format for Elasticsearch
                        // Example: 01-13-02 (Jan 13, 2002) -> 13-01-02
                        if (preg_match('/^(\d{2})-(\d{2})-(\d{2})$/', $value, $matches)) {
                            $month = $matches[1];
                            $day = $matches[2];
                            $year = $matches[3];
                            // Convert MM-dd-yy to dd-MM-yy
                            $doc[$esField] = sprintf('%02d-%02d-%02d', intval($day), intval($month), intval($year));
                        } else {
                            // If format doesn't match, keep as is
                            $doc[$esField] = $value;
                        }
                    } else {
                        $doc[$esField] = $value;
                    }
                }
                $fieldIndex++;
            }
            
            if (!empty($doc)) {
                $documents[] = $doc;
            }
        }
        
        return $documents;
    }
    
    return [];
}

/**
 * Migrate data from SQL file
 */
function migrateFromSQL($sqlFile, $tableToIndex, $fieldMappings) {
    global $es;
    
    $content = file_get_contents($sqlFile);
    $lines = explode("\n", $content);
    
    $currentTable = null;
    $insertBuffer = '';
    $stats = [
        'total' => 0,
        'success' => 0,
        'failed' => 0,
        'by_table' => []
    ];
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Check for INSERT INTO statement
        if (preg_match('/INSERT\s+INTO\s+`?(\w+)`?/i', $line, $matches)) {
            $currentTable = $matches[1];
            $insertBuffer = $line;
            continue;
        }
        
        // Continue building INSERT statement
        if ($currentTable && !empty($line)) {
            $insertBuffer .= ' ' . $line;
            
            // Check if statement is complete
            if (substr(rtrim($line), -1) === ';' || substr(rtrim($line), -1) === ')') {
                if (isset($tableToIndex[$currentTable])) {
                    $indexName = $tableToIndex[$currentTable];
                    $fieldMapping = $fieldMappings[$currentTable];
                    
                    echo "Processing {$currentTable}...\n";
                    
                    $documents = parseInsertStatement($insertBuffer, $currentTable, $fieldMapping);
                    
                    foreach ($documents as $doc) {
                        $id = $doc['id'] ?? null;
                        if ($id === null) {
                            echo "  Warning: No ID found, skipping document\n";
                            continue;
                        }
                        
                        $stats['total']++;
                        
                        // Insert into Elasticsearch
                        $result = $es->index($indexName, $id, $doc);
                        
                        if ($result['success']) {
                            $stats['success']++;
                            if (!isset($stats['by_table'][$currentTable])) {
                                $stats['by_table'][$currentTable] = ['success' => 0, 'failed' => 0];
                            }
                            $stats['by_table'][$currentTable]['success']++;
                        } else {
                            $stats['failed']++;
                            if (!isset($stats['by_table'][$currentTable])) {
                                $stats['by_table'][$currentTable] = ['success' => 0, 'failed' => 0];
                            }
                            $stats['by_table'][$currentTable]['failed']++;
                            echo "  Failed to insert ID {$id}: " . json_encode($result['error']) . "\n";
                        }
                    }
                    
                    echo "  Inserted " . count($documents) . " documents\n";
                }
                
                $currentTable = null;
                $insertBuffer = '';
            }
        }
    }
    
    return $stats;
}

// Run migration
echo "Starting migration from SQL to Elasticsearch...\n";
echo "==============================================\n\n";

$sqlFile = 'sql file/mj.sql';
if (!file_exists($sqlFile)) {
    die("Error: SQL file not found: {$sqlFile}\n");
}

$stats = migrateFromSQL($sqlFile, $tableToIndex, $fieldMappings);

echo "\n==============================================\n";
echo "Migration Complete!\n";
echo "Total documents processed: {$stats['total']}\n";
echo "Successful: {$stats['success']}\n";
echo "Failed: {$stats['failed']}\n";
echo "\nBy Table:\n";
foreach ($stats['by_table'] as $table => $counts) {
    echo "  {$table}: {$counts['success']} success, {$counts['failed']} failed\n";
}

?>

