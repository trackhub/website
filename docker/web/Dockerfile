FROM debian:9

RUN apt-get update && \
    apt-get install -y apache2 curl gnupg2

# usually 1st user have id 1000
# changing the www-data to be with id 1000
# will alllow files editing w/o sudo
RUN usermod -u 1000 www-data
RUN mkdir /home/www-data && \
    chown www-data /home/www-data

RUN usermod -d /home/www-data www-data
RUN chsh -s /bin/bash www-data

COPY virtualhost.conf /etc/apache2/sites-enabled/000-default.conf
COPY virtualhost-ssl.conf /etc/apache2/sites-available/000-default-ssl.conf

RUN a2enmod rewrite ssl

# php
RUN apt-get update && \
    apt-get install -y \
                apt-transport-https lsb-release ca-certificates \
                wget unzip git
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list

ENV PHP_V="7.3"

RUN apt-get update && \
    apt-get install -y php$PHP_V \
                       php${PHP_V}-mysql \
                       php${PHP_V}-xml \
                       php${PHP_V}-intl
RUN update-alternatives --set php /usr/bin/php${PHP_V}
# end php

RUN wget https://getcomposer.org/download/1.7.3/composer.phar -O /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

# js stuff
RUN curl -sL https://deb.nodesource.com/setup_11.x | bash -
RUN apt-get update && apt-get install -y nodejs

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update && \
    apt-get install -y --no-install-recommends yarn
# end js



WORKDIR /var/www

CMD usermod -u $WEB_UID www-data && \
    apachectl -D FOREGROUND
