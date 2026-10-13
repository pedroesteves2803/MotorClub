#!/usr/bin/env bash
set -euo pipefail
umask 077
: "${BACKUP_PASSPHRASE:?Defina uma senha de criptografia forte na variável BACKUP_PASSPHRASE}"
project="${COMPOSE_PROJECT_NAME:-motorclub-staging}"
env_file="${APP_ENV_FILE:-.env.staging}"
output="${BACKUP_DIRECTORY:-backups}"
mkdir -p "$output"
backup_temp=$(mktemp -d)
trap 'rm -rf "$backup_temp"' EXIT
compose=(docker compose --env-file "$env_file" -p "$project" -f compose.production.yaml)
"${compose[@]}" exec -T mysql sh -c 'MYSQL_PWD="$MYSQL_PASSWORD" exec mysqldump --single-transaction --no-tablespaces -u"$MYSQL_USER" "$MYSQL_DATABASE"' > "$backup_temp/database.sql"
"${compose[@]}" exec -T app tar -C /var/www/html/storage/app/private -czf - . > "$backup_temp/photos.tar.gz"
test -s "$backup_temp/database.sql"
tar -czf "$backup_temp/bundle.tar.gz" -C "$backup_temp" database.sql photos.tar.gz
backup_file="$output/motorclub-$(date -u +%Y%m%dT%H%M%SZ)-$$.enc"
openssl enc -aes-256-cbc -salt -pbkdf2 -iter 200000 -in "$backup_temp/bundle.tar.gz" -out "$backup_file" -pass env:BACKUP_PASSPHRASE
printf 'Backup criptografado criado: %s\nCopie para um destino externo e teste a restauração.\n' "$backup_file"
