FROM debian:11

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

ENV PHP_V="8.3"

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

RUN wget https://getcomposer.org/download/2.6.5/composer.phar -O /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer

# js stuff
ENV NODE_MAJOR=21
RUN mkdir /etc/apt/keyrings && \
    curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg && \
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list

# build-essentials are needed by yarn
RUN apt-get update && \
    apt-get install -y nodejs build-essential

RUN npm install --global yarn
# end js

WORKDIR /var/www
