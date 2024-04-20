# "php" stage
FROM php:8.2-apache AS symfony_php


RUN apt-get update

RUN apt-get install --yes \
libzip-dev acl git libsodium-dev unzip zip openssl

RUN docker-php-ext-install zip && docker-php-ext-configure zip
RUN docker-php-ext-install sodium  && docker-php-ext-enable sodium
RUN docker-php-ext-install pdo pdo_mysql

RUN apt-get install dos2unix
RUN apt-get install -y libxml2-dev
RUN docker-php-ext-install soap

# Apache Configuration
RUN a2enmod rewrite
RUN a2enmod headers
RUN a2enmod alias

WORKDIR /var/www/html

RUN curl -sS https://getcomposer.org/installer  | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

COPY ./composer.json ./symfony.lock ./

RUN composer install --no-ansi --dev --no-interaction --no-plugins --no-progress --no-scripts --no-suggest --optimize-autoloader --ignore-platform-reqs
RUN composer dump-autoload  --dev --no-scripts -o

ARG XDEBUG_FILE_LINK_FORMAT
RUN pecl install -f xdebug
RUN echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)\nxdebug.start_with_request=yes" > /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "xdebug.file_link_format='$XDEBUG_FILE_LINK_FORMAT'" >> /usr/local/etc/php/conf.d/xdebug.ini

#RUN pnpm install
COPY contrib/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint
#COPY contrib/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
#RUN chmod +x /usr/local/bin/docker-entrypoint

RUN dos2unix -iso -n /usr/local/bin/docker-entrypoint /usr/local/bin/docker-entrypoint

COPY docker/php8.2-apache/apache.conf /etc/apache2/sites-enabled/000-default.conf
COPY docker/php8.2-apache/php.ini-development /usr/local/etc/php/php.ini

ENTRYPOINT ["docker-entrypoint"]
