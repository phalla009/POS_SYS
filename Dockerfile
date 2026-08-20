FROM php:8.2-fpm

# Install system dependencies, Nginx, and git/zip utilities
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    vim \
    libzip-dev \
    sqlite3 \
    libsqlite3-dev \
    netcat- tradicionales \
    nginx \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Copy Nginx configuration file
COPY nginx.conf /etc/nginx/sites-available/default

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Install PHP dependencies (production optimized)
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Copy wait-for-db script
COPY wait-for-db.sh /usr/local/bin/wait-for-db.sh
RUN chmod +x /usr/local/bin/wait-for-db.sh

# Render dynamically assigns a port, default to 10000 if not specified
ENV PORT=10000

# Expose port (Render uses the PORT env var)
EXPOSE 10000

# Startup script to handle DB wait, migrations, and process launching
CMD ["sh", "-c", "/usr/local/bin/wait-for-db.sh && php artisan config:clear && php artisan migrate --force && php-fpm -D && sed -i \"s/listen 10000;/listen ${PORT};/g\" /etc/nginx/sites-available/default && nginx -g 'daemon off;'"]