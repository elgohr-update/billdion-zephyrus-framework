FROM php:7.4.12-apache-buster

COPY ./ /var/www/html
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini
COPY ./docker/vhosts /etc/apache2/sites-enabled

# Install utilities and libraries
RUN apt-get update && apt-get install -y \
    apt-utils wget build-essential git curl zip openssl dialog locales \
    libonig-dev libcurl4 libcurl4-openssl-dev libsqlite3-dev libsqlite3-0 zlib1g-dev libzip-dev libpq-dev libicu-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install xdebug
RUN pecl install xdebug-2.8.0
RUN docker-php-ext-enable xdebug

# Install APCu PHP extension
RUN pecl install apcu
RUN docker-php-ext-enable apcu --ini-name 10-docker-php-ext-apcu.ini

# Install Freetype and GD extensions
RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql && \
    docker-php-ext-install pdo_sqlite && \
    docker-php-ext-install pdo_pgsql && \
    docker-php-ext-install pgsql && \
    docker-php-ext-install mysqli && \
    docker-php-ext-install curl && \
    docker-php-ext-install tokenizer && \
    docker-php-ext-install json && \
    docker-php-ext-install zip && \
    docker-php-ext-install intl && \
    docker-php-ext-install xml && \
    docker-php-ext-install mbstring && \
    docker-php-ext-install gettext

# Enable apache modules
RUN a2enmod rewrite headers expires

# Install french language locale
RUN locale-gen --no-archive fr_CA.UTF-8 \
    locale -a


RUN composer install