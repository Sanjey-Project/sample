# Enable cURL Extension on Ubuntu with Nginx

## Quick Method (Recommended)

Run this command in terminal:

```bash
sudo apt-get update && sudo apt-get install -y php-curl php8.1-curl && sudo systemctl restart php8.1-fpm && sudo systemctl restart nginx
```

Or use the provided script:
```bash
cd /home/sanjey/Pictures/code/Test
chmod +x enable_curl_nginx.sh
./enable_curl_nginx.sh
```

## Manual Method

### Step 1: Install php-curl package

```bash
sudo apt-get update
sudo apt-get install php-curl php8.1-curl
```

### Step 2: Find PHP-FPM php.ini location

For Nginx, you need to edit the **PHP-FPM** php.ini, not the CLI one:

```bash
# Check PHP-FPM ini location
ls -la /etc/php/8.1/fpm/php.ini
```

Usually located at:
- `/etc/php/8.1/fpm/php.ini` (for PHP-FPM)
- `/etc/php/8.1/cli/php.ini` (for CLI - also edit this)

### Step 3: Edit PHP-FPM php.ini

```bash
sudo nano /etc/php/8.1/fpm/php.ini
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

### Step 4: Also edit CLI php.ini (optional but recommended)

```bash
sudo nano /etc/php/8.1/cli/php.ini
```

Do the same: uncomment `extension=curl`

### Step 5: Restart PHP-FPM and Nginx

```bash
# Restart PHP-FPM
sudo systemctl restart php8.1-fpm

# Restart Nginx
sudo systemctl restart nginx
```

### Step 6: Verify

```bash
# Check if cURL is loaded
php -m | grep curl

# Check PHP-FPM status
sudo systemctl status php8.1-fpm

# Check Nginx status
sudo systemctl status nginx
```

## Important Notes for Nginx

1. **PHP-FPM vs CLI**: Nginx uses PHP-FPM, so you need to edit `/etc/php/8.1/fpm/php.ini`
2. **Restart both services**: Always restart PHP-FPM AND Nginx after changes
3. **Check PHP-FPM version**: Make sure you're editing the correct PHP version

## Find Your PHP-FPM Version

```bash
# Check running PHP-FPM version
ps aux | grep php-fpm

# Or check installed versions
ls /etc/php/
```

## Troubleshooting

### If php-curl package not found:

```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install php8.1-curl
```

### Check PHP-FPM error logs:

```bash
sudo tail -f /var/log/php8.1-fpm.log
```

### Check Nginx error logs:

```bash
sudo tail -f /var/log/nginx/error.log
```

### Verify cURL from web:

Create a test file `test_curl.php`:
```php
<?php
if (extension_loaded('curl')) {
    echo "cURL is enabled";
} else {
    echo "cURL is NOT enabled";
}
phpinfo();
?>
```

Visit it in browser to see full PHP configuration.

## After enabling, verify:

1. Run: `php -m | grep curl` (should show `curl`)
2. Visit: `check_php_requirements.php` in browser
3. Should see: ✓ cURL extension is loaded
4. Should see: ✓ Successfully connected to Elasticsearch

## Common Issues

### Issue: Changes not taking effect
- Make sure you edited `/etc/php/8.1/fpm/php.ini` (not just CLI)
- Restart PHP-FPM: `sudo systemctl restart php8.1-fpm`
- Restart Nginx: `sudo systemctl restart nginx`

### Issue: Wrong PHP version
- Check: `php -v`
- Install for correct version: `sudo apt-get install php8.1-curl` (replace 8.1 with your version)

### Issue: Permission denied
- Use `sudo` for all commands
- Check file permissions: `ls -la /etc/php/8.1/fpm/php.ini`


