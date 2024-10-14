FROM php:8.1-apache

# Set ServerName in Apache configuration
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Update and install necessary packages
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    locales apt-utils git libicu-dev g++ libpng-dev libxml2-dev libzip-dev libonig-dev libxslt-dev unzip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- && \
    mv composer.phar /usr/local/bin/composer

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Install Node.js
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash && \
    apt-get install -y nodejs

RUN npm install --global npm

# Configure and install PHP extensions
RUN docker-php-ext-configure intl && \
    docker-php-ext-install pdo pdo_mysql opcache intl zip calendar dom mbstring gd xsl && \
    pecl install apcu && docker-php-ext-enable apcu

# Set the working directory
WORKDIR /var/www/we_movies
