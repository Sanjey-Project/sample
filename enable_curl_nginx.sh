#!/bin/bash
# Script to enable cURL extension in PHP on Ubuntu with Nginx

echo "Enabling cURL extension for PHP (Nginx setup)..."

# Detect PHP version
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "Detected PHP version: $PHP_VERSION"

# Install php-curl if not installed
echo "Installing php-curl package..."
sudo apt-get update
sudo apt-get install -y php-curl php${PHP_VERSION//./}-curl

# Find php.ini location for PHP-FPM
PHP_INI_CLI=$(php --ini | grep "Loaded Configuration File" | awk '{print $4}')
PHP_INI_FPM="/etc/php/${PHP_VERSION}/fpm/php.ini"

echo "PHP CLI ini file: $PHP_INI_CLI"
echo "PHP-FPM ini file: $PHP_INI_FPM"

# Enable curl in PHP-FPM php.ini
if [ -f "$PHP_INI_FPM" ]; then
    echo "Editing PHP-FPM php.ini..."
    # Uncomment if commented
    sudo sed -i 's/^;extension=curl/extension=curl/' "$PHP_INI_FPM"
    # Add if not exists
    if ! grep -q "^extension=curl" "$PHP_INI_FPM"; then
        echo "extension=curl" | sudo tee -a "$PHP_INI_FPM"
    fi
    echo "✓ Enabled curl extension in PHP-FPM"
else
    echo "Warning: PHP-FPM ini file not found at $PHP_INI_FPM"
fi

# Also enable in CLI php.ini
if [ -f "$PHP_INI_CLI" ]; then
    sudo sed -i 's/^;extension=curl/extension=curl/' "$PHP_INI_CLI"
    echo "✓ Enabled curl extension in CLI"
fi

# Restart PHP-FPM
echo ""
echo "Restarting PHP-FPM..."
sudo systemctl restart php${PHP_VERSION//./}-fpm

# Restart Nginx
echo "Restarting Nginx..."
sudo systemctl restart nginx

echo ""
echo "Done! Please refresh check_php_requirements.php to verify"
echo ""
echo "To verify manually:"
echo "  php -m | grep curl"
echo "  sudo systemctl status php${PHP_VERSION//./}-fpm"


