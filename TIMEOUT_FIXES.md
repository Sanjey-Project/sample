# Timeout Issues - Fixes Applied

## Issues Identified
1. **No timeout settings** in cURL requests
2. **Large query sizes** (10000 documents) causing slow responses
3. **Nested loops** in dashboard queries causing multiple sequential requests
4. **No error handling** for connection failures

## Fixes Applied

### 1. Added Timeout Settings ✅
- **Connection timeout**: 5 seconds
- **Execution timeout**: 30 seconds (60s for bulk operations)
- Added error handling for cURL failures

### 2. Optimized Query Sizes ✅
- Reduced `size: 10000` → `size: 1000` for manage pages
- Reduced `size: 1000` → `size: 500` for AJAX helpers
- Use `size: 0` for count-only queries

### 3. Optimized Dashboard Queries ✅
- **dashboard.php**: Use aggregation for distinct count
- **dashboardfaculty.php**: Batch queries instead of nested loops
- **dashboarddept.php**: Single query with `terms` filter instead of loops

### 4. Improved Error Handling ✅
- Check for cURL errors
- Better error messages
- Graceful fallbacks

## Testing

Run these diagnostic scripts:

1. **Basic connection test**:
   ```
   http://your-domain/test_es_connection.php
   ```

2. **Timeout diagnostic**:
   ```
   http://your-domain/diagnose_timeout.php
   ```

## Additional Recommendations

### If timeouts persist:

1. **Check Elasticsearch performance**:
   ```bash
   curl http://localhost:9200/_cluster/health
   ```

2. **Increase PHP timeout** (if needed):
   ```php
   ini_set('max_execution_time', 60);
   set_time_limit(60);
   ```

3. **Check Elasticsearch logs**:
   ```bash
   tail -f /var/log/elasticsearch/elasticsearch.log
   ```

4. **Optimize Elasticsearch**:
   - Ensure enough heap memory
   - Check disk I/O performance
   - Consider adding more nodes for large datasets

5. **Use pagination** for large result sets:
   ```php
   'from' => 0,
   'size' => 100
   ```

## Performance Targets

- Simple GET: < 100ms
- Small search (10 docs): < 200ms
- Medium search (100 docs): < 500ms
- Large search (1000 docs): < 2000ms
- Count queries: < 100ms

If queries exceed these targets, consider:
- Adding indexes
- Using aggregations
- Implementing caching
- Reducing query complexity

