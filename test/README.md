# PHP to Elasticsearch Conversion - Test Directory

This directory contains PHP files converted from MySQL to Elasticsearch for the Academic Performance Analysis System.

## Files

### Configuration
- **`includes/config_elasticsearch.php`** - Elasticsearch configuration and helper class
  - Replaces `includes/config.php` (MySQL)
  - Provides ElasticsearchClient class with CRUD operations
  - Defines all index constants (1-30)

### Converted Files

1. **`get_student_es.php`** - Get student dropdown (converted from `get_student.php`)
   - Uses Elasticsearch search queries instead of MySQL SELECT
   - Returns students filtered by class ID
   - Returns subjects filtered by class ID

2. **`add_student_es.php`** - Add new student (converted from `add-students.php`)
   - Uses Elasticsearch `index()` method instead of MySQL INSERT
   - Includes form validation and error handling

3. **`manage_students_es.php`** - Manage students list (converted from `manage-students.php`)
   - Uses Elasticsearch search with filters
   - Supports full-text search across multiple fields
   - Includes delete functionality

## Usage

### Basic Operations

#### 1. Index a Document (INSERT equivalent)
```php
$document = [
    'studentId' => 123,
    'studentName' => 'John Doe',
    'rollId' => '900020104100',
    // ... other fields
];
$result = $es->index(INDEX_STUDENTS, 123, $document);
```

#### 2. Search Documents (SELECT equivalent)
```php
$query = [
    'query' => [
        'term' => ['classId' => 1]
    ],
    'size' => 100
];
$result = $es->search(INDEX_STUDENTS, $query);
```

#### 3. Get Document by ID
```php
$result = $es->get(INDEX_STUDENTS, 123);
```

#### 4. Delete Document
```php
$result = $es->delete(INDEX_STUDENTS, 123);
```

## Key Differences: MySQL vs Elasticsearch

| MySQL | Elasticsearch |
|-------|---------------|
| `SELECT * FROM table WHERE id = ?` | `GET /index/_doc/id` |
| `INSERT INTO table VALUES (...)` | `PUT /index/_doc/id` |
| `UPDATE table SET ... WHERE id = ?` | `PUT /index/_doc/id` (same as insert) |
| `DELETE FROM table WHERE id = ?` | `DELETE /index/_doc/id` |
| `SELECT * FROM table WHERE name LIKE '%term%'` | `POST /index/_search` with `match` query |

## Index Mapping

All indexes use numeric names (1-30) as defined in the main project:
- Index 1: students
- Index 2: faculty
- Index 3: classes
- Index 10: administrators
- etc. (see `index_reference.md` in parent directory)

## Testing

1. Ensure Elasticsearch is running on `localhost:9200`
2. Ensure all indexes (1-30) are created
3. Access the PHP files through your web server

## Notes

- The ElasticsearchClient class uses cURL for HTTP requests (no external dependencies)
- All date fields use format: `yyyy-MM-dd HH:mm:ss`
- Search queries support full-text search, filters, sorting, and pagination
- Error handling is included in all operations

## Next Steps

To convert more files:
1. Replace `include('includes/config.php')` with `include('includes/config_elasticsearch.php')`
2. Replace `$dbh->prepare()` with `$es->search()` or `$es->index()`
3. Convert SQL queries to Elasticsearch query DSL
4. Update result handling (Elasticsearch returns JSON structure)

