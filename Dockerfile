FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
        git \
        curl \
        libzip-dev \
        libbz2-dev \
        libjpeg-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        zip \
        unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
        bz2 \
        intl \
        iconv \
        bcmath \
        mbstring \
        pdo_mysql \
        zip \
        exif \
        pcntl \
        gd

COPY . /var/www/html

WORKDIR /var/www/html

COPY ./apache/default.conf /etc/apache2/sites-available/000-default.conf

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN a2enmod rewrite

RUN service apache2 restart
