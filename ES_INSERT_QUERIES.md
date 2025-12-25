# Elasticsearch INSERT Queries Reference

This document shows how to convert MySQL INSERT statements to Elasticsearch operations.

## Table to Index Mapping

| MySQL Table | Elasticsearch Index | Index Number |
|------------|---------------------|--------------|
| `admindata` | `INDEX_ADMINISTRATORS` | 10 |
| `classdata` | `INDEX_CLASSES` | 3 |
| `departmentdata` | `INDEX_DEPARTMENTS` | 9 |
| `facultycombinationdata` | `INDEX_FACULTY_ASSIGNMENTS` | 7 |
| `facultydata` | `INDEX_FACULTY` | 2 |
| `resultdata` | `INDEX_RESULTS` | 5 |
| `studentdata` | `INDEX_STUDENTS` | 1 |
| `studentlogin` | `INDEX_STUDENTS` | 1 (merged with studentdata) |
| `subjectcombinationdata` | `INDEX_CURRICULUM_MAPPINGS` | 8 |
| `subjectdata` | `INDEX_SUBJECTS` | 4 |

## Field Name Mappings

### admindata → INDEX_ADMINISTRATORS
```php
[
    'id' => 'id',
    'FullName' => 'fullName',
    'UserName' => 'userName',
    'email' => 'email',
    'Phno' => 'phoneNumber',
    'Password' => 'password',
    'UpdationDate' => 'updationDate'
]
```

### classdata → INDEX_CLASSES
```php
[
    'id' => 'id',
    'ClassName' => 'className',
    'ClassNameNumeric' => 'classNameNumeric',
    'Section' => 'section',
    'CreationDate' => 'creationDate',
    'UpdationDate' => 'updationDate'
]
```

### departmentdata → INDEX_DEPARTMENTS
```php
[
    'id' => 'id',
    'DepartmentName' => 'departmentName',
    'Username' => 'username',
    'Password' => 'password'
]
```

### facultycombinationdata → INDEX_FACULTY_ASSIGNMENTS
```php
[
    'id' => 'id',
    'FacultyId' => 'facultyId',
    'ClassId' => 'classId',
    'SubjectId' => 'subjectId',
    'status' => 'status',
    'CreationDate' => 'creationDate',
    'UpdationDate' => 'updationDate'
]
```

### facultydata → INDEX_FACULTY
```php
[
    'id' => 'id',
    'FacultyName' => 'facultyName',
    'FacultyCode' => 'facultyCode',
    'Qualification' => 'qualification',
    'contact' => 'contact',
    'Creationdate' => 'creationDate',
    'UpdationDate' => 'updationDate'
]
```

### resultdata → INDEX_RESULTS
```php
[
    'id' => 'id',
    'StudentId' => 'studentId',
    'ClassId' => 'classId',
    'SubjectId' => 'subjectId',
    'marks' => 'marks',
    'Grades' => 'grades',
    'PostingDate' => 'postingDate',
    'UpdationDate' => 'updationDate'
]
```

### studentdata → INDEX_STUDENTS
```php
[
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
]
```

### subjectcombinationdata → INDEX_CURRICULUM_MAPPINGS
```php
[
    'id' => 'id',
    'ClassId' => 'classId',
    'SubjectId' => 'subjectId',
    'status' => 'status',
    'CreationDate' => 'creationDate',
    'Updationdate' => 'updationDate'
]
```

### subjectdata → INDEX_SUBJECTS
```php
[
    'id' => 'id',
    'SubjectName' => 'subjectName',
    'SubjectCode' => 'subjectCode',
    'credit' => 'credit',
    'semester' => 'semester',
    'Creationdate' => 'creationDate',
    'UpdationDate' => 'updationDate'
]
```

### studentlogin → INDEX_STUDENTS
```php
[
    'Id' => 'id',
    'Password' => 'password'
]
```
**Note:** The `studentlogin` table has no INSERT statements in mj.sql (empty table). 
In Elasticsearch, student login credentials are typically merged with student data in `INDEX_STUDENTS`.
If you need to insert student login data separately, merge it with existing student records.

## Example Conversions

### MySQL INSERT
```sql
INSERT INTO `admindata` (`id`, `FullName`, `UserName`, `email`, `Phno`, `Password`, `UpdationDate`) 
VALUES (1, 'GokuSan', 'Songoku', 'sanjeykannaa12@gmail.com', 9629530722, 'Sanjey12@', '2024-05-01 14:22:58');
```

### Elasticsearch Equivalent
```php
$document = [
    'id' => 1,
    'fullName' => 'GokuSan',
    'userName' => 'Songoku',
    'email' => 'sanjeykannaa12@gmail.com',
    'phoneNumber' => 9629530722,
    'password' => 'Sanjey12@',
    'updationDate' => '2024-05-01 14:22:58'
];

$result = $es->index(INDEX_ADMINISTRATORS, 1, $document);
```

### MySQL INSERT (Multiple Rows)
```sql
INSERT INTO `classdata` (`id`, `ClassName`, `ClassNameNumeric`, `Section`, `CreationDate`, `UpdationDate`) 
VALUES
(1, 'CSE', 1, 'A', '2024-05-04 04:01:37', '2024-05-04 04:01:37'),
(2, 'CSE', 1, 'B', '2024-05-04 04:03:04', '2024-05-04 04:03:04');
```

### Elasticsearch Equivalent
```php
// Option 1: Individual inserts
$doc1 = [
    'id' => 1,
    'className' => 'CSE',
    'classNameNumeric' => 1,
    'section' => 'A',
    'creationDate' => '2024-05-04 04:01:37',
    'updationDate' => '2024-05-04 04:01:37'
];
$es->index(INDEX_CLASSES, 1, $doc1);

$doc2 = [
    'id' => 2,
    'className' => 'CSE',
    'classNameNumeric' => 1,
    'section' => 'B',
    'creationDate' => '2024-05-04 04:03:04',
    'updationDate' => '2024-05-04 04:03:04'
];
$es->index(INDEX_CLASSES, 2, $doc2);

// Option 2: Bulk insert
$operations = [
    ['action' => ['index' => ['_index' => INDEX_CLASSES, '_id' => 1]], 'data' => $doc1],
    ['action' => ['index' => ['_index' => INDEX_CLASSES, '_id' => 2]], 'data' => $doc2]
];
$es->bulk($operations);
```

## Usage

### Run Migration Script
```bash
php migrate_all_data.php
```

This will automatically:
1. Parse all INSERT statements from `sql file/mj.sql`
2. Convert field names to Elasticsearch format
3. Insert all documents into appropriate indexes
4. Show statistics of successful/failed inserts

### Manual Insert Example
```php
<?php
include('includes/config_elasticsearch.php');

// Insert a single admin
$admin = [
    'id' => 1,
    'fullName' => 'GokuSan',
    'userName' => 'Songoku',
    'email' => 'sanjeykannaa12@gmail.com',
    'phoneNumber' => 9629530722,
    'password' => md5('Sanjey12@'), // Hash password
    'updationDate' => '2024-05-01 14:22:58'
];

$result = $es->index(INDEX_ADMINISTRATORS, 1, $admin);
if ($result['success']) {
    echo "Admin inserted successfully\n";
} else {
    echo "Error: " . json_encode($result['error']) . "\n";
}
?>
```

## Notes

1. **ID Field**: The `id` field is used as the document ID in Elasticsearch
2. **Type Conversion**: Numeric fields are converted to integers
3. **NULL Values**: NULL values are skipped (not included in document)
4. **Password Hashing**: Consider hashing passwords before inserting
5. **Bulk Operations**: For large datasets, use bulk operations for better performance

