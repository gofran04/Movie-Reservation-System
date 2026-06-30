FROM php:8.3-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

CMD ["php-fpm"]