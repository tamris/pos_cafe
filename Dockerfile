FROM php:8.2-cli

# Install dependencies dan ekstensi PHP untuk Laravel & MySQL
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Install dependencies composer
RUN composer install --no-dev --optimize-autoloader

EXPOSE 10000

# Jalankan Laravel di port 10000 (port standar Render)
CMD sh -c "php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"