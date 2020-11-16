FROM php:7.4-fpm-alpine3.12
RUN apk update --no-cache \
    && apk add --no-cache $PHPIZE_DEPS \
    && apk add --no-cache sqlite