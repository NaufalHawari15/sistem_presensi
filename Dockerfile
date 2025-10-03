FROM php:8.3-apache

# Install extension & dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install intl zip \
    && docker-php-ext-enable intl zip \
    && a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . .

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer
RUN composer install --optimize-autoloader --no-scripts --no-interaction

# Permission untuk Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Railway kasih $PORT
ENV PORT=8080

# Set Apache listen di $PORT (bukan 80)
RUN sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf \
    && sed -i "s/:80/:${PORT}/" /etc/apache2/sites-available/000-default.conf

EXPOSE ${PORT}

# Jalankan Apache
CMD ["apache2-foreground"]
