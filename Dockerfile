FROM php:7.4.15-fpm-alpine3.13
COPY . .
COPY pool.conf /usr/local/etc/php-fpm.d/www.conf
ENV NR_INSTALL_USE_CP_NOT_LN=1
ENV NR_INSTALL_SILENT=1
RUN wget https://getcomposer.org/download/1.10.17/composer.phar -O composer.phar
RUN apk update
RUN apk add --no-cache zip libzip-dev
RUN apk add --no-cache freetype libpng libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev git ca-certificates
RUN apk add --no-cache libgcc libstdc++ libx11 glib libxrender libxext libintl ttf-dejavu ttf-droid ttf-freefont ttf-liberation ttf-ubuntu-font-family && \
    apk add --no-cache libcrypto1.0 libssl1.0 --repository=http://dl-cdn.alpinelinux.org/alpine/v3.8/main
RUN wget https://github.com/madnight/docker-alpine-wkhtmltopdf/raw/auto-build/wkhtmltopdf -O /usr/bin/wkhtmltopdf && chmod +x /usr/bin/wkhtmltopdf && ln -s /usr/bin/wkhtmltopdf /usr/local/bin/wkhtmltopdf-amd64
RUN docker-php-ext-install gd
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-enable opcache
RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip
RUN php composer.phar install

WORKDIR /var/www/html

RUN chmod 777 -R /var/www/html/storage
RUN cp .env.example .env
COPY opcache.ini $PHP_INI_DIR/conf.d/
RUN wget -O newrelic-php5-9.17.1.301-linux-musl.tar.gz "https://drive.google.com/uc?export=download&id=1ATsidpdf_K2LKaNp8jbhYagU2hGq_15x" && ls && \
    tar zxf newrelic-php5-9.17.1.301-linux-musl.tar.gz && cd newrelic-php5-9.17.1.301-linux-musl && ./newrelic-install install

RUN sed -i -e "s/newrelic.license =.*/newrelic.license = 67407bd45d3ebabc1977d0dc41034601FFFFNRAL/" -e "s/newrelic.appname =.*/newrelic.appname = Silosys - API TEST/" /usr/local/etc/php/conf.d/newrelic.ini