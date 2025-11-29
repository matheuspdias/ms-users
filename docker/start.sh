#!/bin/bash

# Start Supervisor in the background
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf

# Start PHP-FPM in the foreground
php-fpm
