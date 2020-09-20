FROM alpine:3.12
ENV COMPOSER_HOME=/var/cache/composer

RUN apk --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/edge/community add \
        nginx supervisor curl zip rsync xz coreutils \
        php7 php7-fpm \
        php7-ctype php7-curl php7-dom php7-fileinfo php7-gd php7-gmp \
        php7-iconv php7-intl php7-json php7-mbstring php7-openssl php7-bcmath \
        php7-session php7-simplexml php7-tokenizer php7-xml php7-xmlreader php7-xmlwriter \
        php7-zip php7-zlib php7-phar php7-sockets \
        gnu-libiconv \
    && rm /etc/nginx/conf.d/default.conf

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

COPY docker/config/etc /etc

WORKDIR /app

ADD . .

RUN composer global require hirak/prestissimo
RUN composer install --no-dev --no-interaction -o

# Expose the port nginx is reachable on
EXPOSE 8000

# Let supervisord start nginx & php-fpm
ENTRYPOINT ["./docker/entrypoint.sh"]

# Configure a healthcheck to validate that everything is up&running
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8000/fpm-ping
