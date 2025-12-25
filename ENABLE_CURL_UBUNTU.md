# Enable cURL Extension on Ubuntu

## Quick Method (Recommended)

Run this command in terminal:

```bash
sudo apt-get update && sudo apt-get install -y php-curl && sudo systemctl restart apache2
```

Or use the provided script:
```bash
cd /home/sanjey/Pictures/code/Test
chmod +x enable_curl_ubuntu.sh
./enable_curl_ubuntu.sh
```

## Manual Method

### Step 1: Install php-curl package

```bash
sudo apt-get update
sudo apt-get install php-curl
```

### Step 2: Find php.ini location

```bash
php --ini
```

Look for "Loaded Configuration File" - usually:
- `/etc/php/8.1/apache2/php.ini` (for Apache)
- `/etc/php/8.1/cli/php.ini` (for CLI)

### Step 3: Edit php.ini

```bash
sudo nano /etc/php/8.1/apache2/php.ini
```

Find this line (use Ctrl+W to search):
```
;extension=curl
```

Remove the semicolon:
```
extension=curl
```

Save: Ctrl+O, Enter, Ctrl+X

### Step 4: Restart Apache

```bash
sudo systemctl restart apache2
```

### Step 5: Verify

```bash
php -m | grep curl
```

Should output: `curl`

Or visit: `check_php_requirements.php` again

## Alternative: Enable for all PHP versions

```bash
# Install for all PHP versions
sudo apt-get install php8.1-curl php8.0-curl php7.4-curl

# Or install for specific version
sudo apt-get install php8.1-curl

# Restart Apache
sudo systemctl restart apache2
```

## If using PHP-FPM (Nginx)

```bash
sudo apt-get install php8.1-curl
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx
```

## Troubleshooting

### If "php-curl" package not found:

```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install php8.1-curl
```

### Check if cURL is enabled:

```bash
php -r "echo extension_loaded('curl') ? 'cURL enabled' : 'cURL NOT enabled';"
```

### Check Apache error logs:

```bash
sudo tail -f /var/log/apache2/error.log
```

## After enabling, verify:

1. Run: `php -m | grep curl`
2. Visit: `check_php_requirements.php`
3. Should see: ✓ cURL extension is loaded


