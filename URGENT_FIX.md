# URGENT: Pages Not Loading - Quick Fix

## Problem
Pages are hanging/not loading, even `index.php` which has no PHP includes.

## Root Cause
PHP-FPM might be hanging when processing requests, possibly due to Elasticsearch connection timeouts.

## Immediate Fix Applied

1. **Reduced timeouts to 1-3 seconds** (was 2-10 seconds)
2. **Added CURLOPT_NOSIGNAL** to prevent signal issues
3. **Added error handling** for curl_init failures
4. **Made config initialization lazy** to prevent blocking

## Test These URLs

1. **Minimal test** (no includes):
   ```
   http://your-domain/minimal_test.php
   ```

2. **Diagnostic**:
   ```
   http://your-domain/diagnose_hang.php
   ```

3. **Original index**:
   ```
   http://your-domain/index.php
   ```

## If Still Hanging

### Option 1: Restart PHP-FPM
```bash
sudo systemctl restart php8.1-fpm
```

### Option 2: Check PHP-FPM Pool Status
```bash
sudo systemctl status php8.1-fpm
```

### Option 3: Temporarily Disable Elasticsearch Config
Rename the config file temporarily:
```bash
mv includes/config_elasticsearch.php includes/config_elasticsearch.php.bak
```

Then test if `index.php` loads (it should, since it doesn't include config).

### Option 4: Check PHP-FPM Logs
```bash
sudo tail -f /var/log/php8.1-fpm.log
```

Then try accessing a page and see what errors appear.

## Quick Diagnostic Commands

```bash
# Test Elasticsearch directly
curl -m 2 http://localhost:9200

# Test PHP CLI
php -r "echo 'PHP works';"

# Check if services are running
ps aux | grep php-fpm
ps aux | grep nginx
```

## Expected Behavior

- `minimal_test.php` should load instantly (< 100ms)
- `index.php` should load instantly (< 100ms) 
- Pages with Elasticsearch queries may take 1-3 seconds max

If pages still hang, the issue is likely with PHP-FPM configuration or a system-level problem.

