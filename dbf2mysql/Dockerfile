FROM php:7.4-apache

# Install required dependencies
RUN apt-get update && apt-get upgrade -y\
    apt-get install -y libdb-dev && \
    apt-get install -y libzip-dev && \
    apt-get install -y zlib1g-dev && \
    apt-get install -y libicu-dev

# Install mysqli extension
RUN docker-php-ext-install mysqli && \
    docker-php-ext-enable mysqli

# Install pdo_mysql extension
RUN docker-php-ext-install pdo_mysql && \
    docker-php-ext-enable pdo_mysql

# Install dbase extension
RUN pecl install dbase && \
    docker-php-ext-enable dbase

# Enable other required extensions
RUN docker-php-ext-install zip && \
    docker-php-ext-install intl

# Copy Apache vhost file to proxy PHP requests
COPY apache.conf /etc/apache2/sites-available/000-default.conf
