FROM php:8.2-alpine as code_base

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /application

RUN addgroup -S composer \
    && adduser -S composer -G composer \
    && chown -R composer /application

RUN apk add --no-cache libzip libpng libjpeg-turbo libwebp freetype postgresql-client postgresql-dev

RUN apk add --virtual build-dependencies --no-cache \
    icu-dev icu-libs zlib-dev g++ make automake autoconf libzip-dev \
    libpng-dev libwebp-dev libjpeg-turbo-dev freetype-dev postgresql-client postgresql-dev \
    && docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install zip \
    && docker-php-ext-install pdo \
    && docker-php-ext-install pdo_pgsql \
    && docker-php-ext-install opcache \
    && docker-php-ext-install gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del build-dependencies && rm -rf /usr/src/php*

USER composer

COPY --chown=composer composer.json composer.lock ./

RUN composer install --no-interaction --prefer-dist --no-autoloader --no-scripts

COPY --chown=composer . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

FROM node:18-alpine AS node_code_base

WORKDIR /application

COPY --from=code_base /application /application

RUN npm install --frozen-lockfile && npm run build

FROM php:8.2-alpine as cli

WORKDIR /application

RUN apk add --no-cache libzip libpng libjpeg-turbo libwebp freetype postgresql-client postgresql-dev

RUN apk add --virtual build-dependencies --no-cache \
    icu-dev icu-libs zlib-dev g++ make automake autoconf libzip-dev \
    libpng-dev libwebp-dev libjpeg-turbo-dev freetype-dev postgresql-client postgresql-dev \
    && docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install zip \
    && docker-php-ext-install pdo \
    && docker-php-ext-install pdo_pgsql \
    && docker-php-ext-install opcache \
    && docker-php-ext-install gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del build-dependencies && rm -rf /usr/src/php*

COPY --from=node_code_base /application /application

FROM php:8.2-fpm-alpine as fpm_server

WORKDIR /application

RUN apk add --no-cache libzip libpng libjpeg-turbo libwebp freetype postgresql-client postgresql-dev

RUN apk add --virtual build-dependencies --no-cache \
    icu-dev icu-libs zlib-dev g++ make automake autoconf libzip-dev \
    libpng-dev libwebp-dev libjpeg-turbo-dev freetype-dev postgresql-client postgresql-dev \
    && docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install zip \
    && docker-php-ext-install pdo \
    && docker-php-ext-install pdo_pgsql \
    && docker-php-ext-install opcache \
    && docker-php-ext-install gd \
    && docker-php-ext-install intl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del build-dependencies && rm -rf /usr/src/php*

COPY --from=node_code_base --chown=www-data:www-data /application /application

RUN php artisan event:cache && \
    php artisan route:cache && \
    php artisan view:cache

USER root

RUN chmod -R 775 /application/storage

USER www-data

FROM nginx:alpine as web_server

WORKDIR /application

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

COPY --from=node_code_base /application/public /application/public
