# Gunakan PHP dengan Apache
FROM php:8.3-apache

# Install dependency sistem & ekstensi PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install intl zip \
    && docker-php-ext-enable intl zip

# Aktifkan mod_rewrite Apache (untuk Laravel routing)
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy source code
COPY . .

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# Install dependencies Laravel
RUN composer install --optimize-autoloader --no-scripts --no-interaction

# Permission storage & bootstrap
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Expose port Railway
EXPOSE 8080

# Update Apache listen ke $PORT
RUN sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf \
    && sed -i "s/:80/:${PORT}/" /etc/apache2/sites-available/000-default.conf

EXPOSE ${PORT}

# Start Apache
CMD ["apache2-foreground"]
