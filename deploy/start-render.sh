#!/bin/sh

set -eu

# Render exposes the HTTP port at runtime. Apache's base image listens on 80,
# so update its two configuration points before starting the web server.
port="${PORT:-10000}"
sed -ri "s/^Listen [0-9]+$/Listen ${port}/" /etc/apache2/ports.conf
sed -ri "s#<VirtualHost \*:[0-9]+>#<VirtualHost *:${port}>#" /etc/apache2/sites-available/000-default.conf

if [ -n "${DB_SSL_CA_B64:-}" ]; then
    printf '%s' "$DB_SSL_CA_B64" | base64 --decode > /tmp/motorclub-db-ca.pem
    # Apache workers run as www-data, so they need read access to the public CA.
    chmod 644 /tmp/motorclub-db-ca.pem
    export MYSQL_ATTR_SSL_CA=/tmp/motorclub-db-ca.pem
fi

php artisan migrate --force

exec apache2-foreground
