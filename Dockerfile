FROM php:8.2-apache

# Install system deps and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libgd-dev \
    && docker-php-ext-install pdo pdo_mysql zip gd

# Enable apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html

# If you want composer install to run during build
RUN composer install --no-dev --optimize-autoloader

# Install dependencies for the app directory
RUN cd app && composer install --no-dev --optimize-autoloader

# Set permissions (adjust as needed)
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && find /var/www/html -type d -exec chmod 755 {} \;

EXPOSE 80
CMD ["apache2-foreground"]