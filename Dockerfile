FROM php:8.4-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    openssh-client \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install pgsql pdo_pgsql

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY --from=composer/composer:latest /usr/bin/composer /usr/local/bin/composer
COPY . /app
COPY .env /app/.env
RUN composer install

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
