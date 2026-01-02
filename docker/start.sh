#!/bin/bash

# Fix storage permissions
chown -R www-data:www-data /var/www/html/storage

# Start Supervisor in the background
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf

# Start PHP-FPM in the foreground
php-fpm
