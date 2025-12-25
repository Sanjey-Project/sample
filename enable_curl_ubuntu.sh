#!/bin/bash
# Script to enable cURL extension in PHP on Ubuntu

echo "Enabling cURL extension for PHP..."

# Detect PHP version
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "Detected PHP version: $PHP_VERSION"

# Install php-curl if not installed
echo "Installing php-curl package..."
sudo apt-get update
sudo apt-get install -y php-curl

# Find php.ini location
PHP_INI=$(php --ini | grep "Loaded Configuration File" | awk '{print $4}')
echo "PHP ini file: $PHP_INI"

# Check if extension=curl exists
if grep -q "^extension=curl" "$PHP_INI" || grep -q "^;extension=curl" "$PHP_INI"; then
    echo "Found curl extension line in php.ini"
    # Uncomment if commented
    sudo sed -i 's/^;extension=curl/extension=curl/' "$PHP_INI"
    echo "✓ Enabled curl extension"
else
    echo "Adding curl extension to php.ini..."
    echo "extension=curl" | sudo tee -a "$PHP_INI"
fi

# Restart web server
echo ""
echo "Restarting web server..."
if systemctl is-active --quiet apache2; then
    sudo systemctl restart apache2
    echo "✓ Apache restarted"
elif systemctl is-active --quiet nginx; then
    sudo systemctl restart php-fpm
    echo "✓ PHP-FPM restarted"
else
    echo "Please restart your web server manually"
fi

echo ""
echo "Done! Please refresh check_php_requirements.php to verify"


