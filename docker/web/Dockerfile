FROM debian:9

RUN apt-get update && \
    apt-get install -y apache2 curl gnupg2 netcat \
    # php stuff
    apt-transport-https lsb-release ca-certificates wget unzip git

RUN mkdir /home/www-data && \
    chown www-data /home/www-data

RUN usermod -d /home/www-data www-data
RUN chsh -s /bin/bash www-data

COPY virtualhost.conf /etc/apache2/sites-enabled/000-default.conf
COPY virtualhost-ssl.conf /etc/apache2/sites-available/000-default-ssl.conf

RUN a2enmod rewrite ssl expires

# php
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list

ENV PHP_V="7.4"

RUN apt-get update && \
    apt-get install -y php$PHP_V \
                       php${PHP_V}-mysql \
                       php${PHP_V}-xml \
                       php${PHP_V}-curl \
                       php${PHP_V}-mbstring \
                       php${PHP_V}-imagick \
                       php${PHP_V}-exif \
                       php${PHP_V}-intl

RUN update-alternatives --set php /usr/bin/php${PHP_V}
COPY php.ini /etc/php/${PHP_V}/apache2/php.ini
# end php

RUN wget https://getcomposer.org/download/1.10.16/composer.phar -O /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

# js stuff
RUN curl -sL https://deb.nodesource.com/setup_12.x | bash -
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -

RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list

RUN apt-get update && \
    apt-get install -y nodejs && \
    apt-get install -y --no-install-recommends yarn
# end js

WORKDIR /var/www
