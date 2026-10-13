FROM php:8.4-apache-bookworm AS application
RUN apt-get update && apt-get install -y --no-install-recommends libpng-dev libjpeg62-turbo-dev libwebp-dev libzip-dev libonig-dev libicu-dev unzip \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install gd pdo_mysql mbstring zip intl opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/private bootstrap/cache \
    && composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
    && rm -rf public/images/demo \
    && chown -R www-data:www-data storage bootstrap/cache
COPY deploy/apache.conf /etc/apache2/sites-available/000-default.conf
COPY deploy/php.ini /usr/local/etc/php/conf.d/motorclub.ini
COPY deploy/start-render.sh /usr/local/bin/start-render
RUN chmod +x /usr/local/bin/start-render

FROM node:24-bookworm-slim AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
COPY --from=application /var/www/html/vendor vendor
RUN npm run build


FROM application AS production
COPY --from=assets /app/public/build public/build
