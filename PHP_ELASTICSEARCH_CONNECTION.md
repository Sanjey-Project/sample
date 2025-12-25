# PHP to Elasticsearch Connection Guide

## Current Implementation

The project uses **cURL** (built into PHP) to connect to Elasticsearch. No additional packages are required if cURL is enabled.

## Check Requirements

Visit: `check_php_requirements.php` to verify:
- PHP version (7.2+)
- cURL extension enabled
- JSON extension enabled
- Elasticsearch connection

## Option 1: Using cURL (Current - No Installation Needed)

### Requirements
- PHP 7.2 or higher
- cURL extension enabled
- JSON extension enabled

### Enable cURL (if not enabled)

**Linux (XAMPP/LAMP):**
```bash
# Edit php.ini
sudo nano /opt/lampp/etc/php.ini

# Find and uncomment:
extension=curl

# Restart Apache
sudo /opt/lampp/lampp restart
```

**Windows (XAMPP):**
1. Open `C:\xampp\php\php.ini`
2. Find: `;extension=curl`
3. Remove the semicolon: `extension=curl`
4. Restart Apache from XAMPP Control Panel

**Check if cURL is enabled:**
```php
<?php
if (extension_loaded('curl')) {
    echo "cURL is enabled";
} else {
    echo "cURL is NOT enabled";
}
?>
```

### Test Connection

Visit: `check_php_requirements.php` in your browser

## Option 2: Using Official Elasticsearch PHP Client (Recommended for Production)

### Installation

```bash
# Install Composer (if not installed)
curl -sS https://getcomposer.org/installer | php

# Install Elasticsearch PHP client
composer require elasticsearch/elasticsearch
```

### Usage

Replace in your PHP files:
```php
// OLD:
include("includes/config_elasticsearch.php");

// NEW:
include("includes/config_elasticsearch_composer.php");
```

### Benefits
- Official client library
- Better error handling
- More features
- Better performance
- Active maintenance

## Troubleshooting

### Issue: "Call to undefined function curl_init()"

**Solution:** Enable cURL extension
- Edit `php.ini`
- Uncomment `extension=curl`
- Restart web server

### Issue: "Cannot connect to Elasticsearch"

**Check:**
1. Elasticsearch is running: `curl http://localhost:9200`
2. Correct host/port in `config_elasticsearch.php`
3. Firewall allows port 9200
4. PHP can make HTTP requests

### Issue: "cURL error: Connection refused"

**Solutions:**
- Ensure Elasticsearch is running
- Check ES_HOST and ES_PORT in config
- Try: `telnet localhost 9200`

### Test Connection Manually

```bash
# Test Elasticsearch
curl http://localhost:9200

# Test from PHP
php -r "echo file_get_contents('http://localhost:9200');"
```

## Quick Fix Checklist

- [ ] PHP 7.2+ installed
- [ ] cURL extension enabled (`php -m | grep curl`)
- [ ] JSON extension enabled (`php -m | grep json`)
- [ ] Elasticsearch running on port 9200
- [ ] Can access: http://localhost:9200
- [ ] Run: `check_php_requirements.php`
- [ ] Check error logs if connection fails

## Current Status

The current implementation (`config_elasticsearch.php`) uses cURL which:
- ✅ Works without installing packages
- ✅ Built into PHP
- ✅ Lightweight
- ✅ Compatible with all PHP versions 7.2+

**No additional installation needed** - just ensure cURL is enabled in PHP!


