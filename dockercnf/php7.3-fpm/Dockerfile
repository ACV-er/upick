FROM php:7.3-fpm
COPY redis /usr/src/php/ext/redis
COPY sources.list /etc/apt/sources.list
RUN rm -Rf /var/lib/apt/lists/* \
    && sed -i "s@listen = 9000@;listen = 9000@g" /usr/local/etc/php-fpm.d/zz-docker.conf \
    && apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-install -j$(nproc) bcmath \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install -j$(nproc) pdo_mysql \
    && docker-php-ext-install -j$(nproc) redis \
    && docker-php-ext-install -j$(nproc) mysqli \
	&& docker-php-ext-install -j$(nproc) opcache
