FROM php:8.5-cli-alpine

RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        zip \
        intl \
        bcmath \
        mbstring \
        pcntl \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

