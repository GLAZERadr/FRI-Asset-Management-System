FROM richarvey/nginx-php-fpm:3.1.6

# Install ImageMagick and PHP imagick extension
RUN apk add --no-cache \
    imagemagick \
    imagemagick-dev \
    && apk add --no-cache --virtual .build-deps \
    autoconf \
    g++ \
    make \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apk del .build-deps

COPY . .

# Image config
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Laravel config
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

CMD ["/start.sh"]