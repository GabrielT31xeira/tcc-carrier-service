FROM php:8.2-fpm

ARG UID
ARG GID

ENV UID=${UID}
ENV GID=${GID}

RUN addgroup --system --gid ${GID} carrier
RUN adduser --system --home /bin/sh --no-create-home --uid ${UID} --ingroup carrier --disabled-login --disabled-password carrier

RUN sed -i 's/user = www-data/user = carrier/g' /usr/local/etc/php-fpm.d/www.conf
RUN sed -i 's/group = www-data/group = carrier/g' /usr/local/etc/php-fpm.d/www.conf

RUN chown -R ${UID}:${GID} /var/www/html

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    jpegoptim optipng pngquant gifsicle \
    vim \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libzip-dev \
    libexif-dev \
    libonig-dev \
    autoconf \
    pkg-config \
    libssl-dev

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

EXPOSE 9000

USER carrier
