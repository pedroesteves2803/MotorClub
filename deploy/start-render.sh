#!/bin/sh

set -eu

# Render exposes the HTTP port at runtime. Apache's base image listens on 80,
# so update its two configuration points before starting the web server.
port="${PORT:-10000}"
sed -ri "s/^Listen [0-9]+$/Listen ${port}/" /etc/apache2/ports.conf
sed -ri "s#<VirtualHost \*:[0-9]+>#<VirtualHost *:${port}>#" /etc/apache2/sites-available/000-default.conf

php artisan migrate --force

exec apache2-foreground
