FROM php:8.2-fpm-alpine3.16

RUN set -ex && apk --no-cache add postgresql-dev
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install pdo pdo_pgsql
RUN sed -i -e 's/;extension=pdo_pgsql/extension=pdo_pgsql/' /usr/local/etc/php/php.ini-production
RUN apk del postgresql-dev
RUN apk add --upgrade postgresql --update-cache --repository http://dl-3.alpinelinux.org/alpine/edge/main/

WORKDIR /var/www

EXPOSE 9000