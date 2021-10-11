#syntax=docker/dockerfile:1.2


ARG PHP_VERSION=8.0.11
# ARG PHP_VERSION=7.4
FROM php:${PHP_VERSION}-fpm-alpine

RUN mkdir /backend_cnt
RUN mkdir /backend_cnt/vendor

WORKDIR /backend_cnt

RUN chown -R www-data:www-data /backend_cnt

COPY --chown=www-data:www-data ./ .

# Install required deps
RUN apk add --update --no-cache \
    libzip \
    libpng \
    libwebp \
    libjpeg \
    libxpm \
    libgd \
    freetype \
    libpng-dev \
    libwebp-dev \
    libxpm-dev \
    freetype-dev \
    curl \
    libxml2 \
    oniguruma \
    libzip-dev \
    curl-dev \
    libxml2-dev \
    oniguruma-dev \
    autoconf \
    g++ \
    make \
    shadow

RUN  wget -q -O /usr/local/bin/install-php-extensions https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions 

RUN chmod uga+x /usr/local/bin/install-php-extensions
RUN sync 
RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    curl \
    soap \
    # pdo \
    # mysqli \
    # dom \
    # json \
    # xml \
    # bcmath \
    # gd

# Configure PHP Pecl extensions
RUN pecl channel-update pecl.php.net; \
    pecl install redis \
    &&  rm -rf /tmp/pear \
    # Enable them via helper script
    &&  docker-php-ext-enable redis;

# # Use the default production configuration
# RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# # Clean-up unnecessary stuff
RUN apk del --purge \
    g++ \
    make \
    libzip-dev \
    curl-dev \
    libxml2-dev \
    oniguruma-dev \
    libpng-dev \
    libwebp-dev \
    libxpm-dev \
    freetype-dev \
    autoconf

RUN apk add --update --no-cache composer

USER www-data

RUN composer install

ENTRYPOINT [ "php", "artisan", "serve", "--host", "0.0.0.0" ]