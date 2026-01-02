#!/bin/bash

# Fix permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/routes

# Start Supervisor in the background
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf

# Start PHP-FPM in the foreground
php-fpm
