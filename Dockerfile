FROM php:8.2-apache

# Copy project files to Apache root
COPY . /var/www/html/

# Install mysqli extension for MySQL connection
RUN docker-php-ext-install mysqli

# Enable Apache rewrite module
RUN a2enmod rewrite