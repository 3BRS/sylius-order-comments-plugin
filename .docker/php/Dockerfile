FROM node:14-alpine AS node

FROM php:8.0-fpm-alpine

RUN apk add --no-cache \
    bash \
    git \
    make \
    python3 \
    g++ \
    icu-dev \
    libzip-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        intl \
        pdo_mysql \
        gd \
        zip \
        opcache \
        mbstring \
        exif

COPY --from=node /usr/lib /usr/lib
COPY --from=node /usr/local/share /usr/local/share
COPY --from=node /usr/local/lib /usr/local/lib
COPY --from=node /usr/local/include /usr/local/include
COPY --from=node /usr/local/bin /usr/local/bin
COPY --from=node /opt /opt

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

CMD [ "php-fpm" ]
