# Dockerfile

FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev libonig-dev libxml2-dev \
    libpq-dev libicu-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libmcrypt-dev libxslt1-dev libssl-dev mariadb-client \
    curl npm nodejs

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip intl

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy app code
COPY . .

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Install Node dependencies and build assets
RUN npm install && npm run build

# Symfony CLI (optional for local dev)
# RUN curl -sS https://get.symfony.com/cli/installer | bash

# Entrypoint: migrate + load fixtures + run server
CMD php bin/console doctrine:schema:update --force && \
    php bin/console doctrine:fixtures:load --no-interaction && \
    php -S 0.0.0.0:10000 -t public
