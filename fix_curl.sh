#!/bin/bash
# Script to enable cURL in PHP-FPM php.ini

echo "Enabling cURL extension in PHP-FPM..."

# Edit PHP-FPM php.ini
sudo sed -i 's/^;extension=curl/extension=curl/' /etc/php/8.1/fpm/php.ini

# Also enable in CLI php.ini
sudo sed -i 's/^;extension=curl/extension=curl/' /etc/php/8.1/cli/php.ini

# Restart PHP-FPM
echo "Restarting PHP-FPM..."
sudo systemctl restart php8.1-fpm

# Restart Nginx
echo "Restarting Nginx..."
sudo systemctl restart nginx

echo ""
echo "Done! cURL should now be enabled."
echo "Please refresh check_php_requirements.php to verify"


