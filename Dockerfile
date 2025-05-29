FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory
COPY . .

# Copy .env file
COPY .env.example .env

# Install dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Generate key
RUN php artisan key:generate

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configure Apache
RUN a2enmod rewrite headers expires
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Create database directory and set permissions
RUN mkdir -p /var/www/html/database \
    && rm -f /var/www/html/database/database.sqlite \
    && touch /var/www/html/database/database.sqlite \
    && chown -R www-data:www-data /var/www/html/database \
    && chmod -R 775 /var/www/html/database

# Run migrations
RUN php artisan migrate --force

# Create storage link
RUN php artisan storage:link

# Verify public directory
RUN ls -la /var/www/html/public

# Expose port 80
EXPOSE 80

# Start Apache with debug output
CMD ["apache2-foreground"] 