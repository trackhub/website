FROM debian:9

RUN apt-get update && \
    apt-get install -y apache2

# usually 1st user have id 1000
# changing the www-data to be with id 1000
# will alllow files editing w/o sudo
RUN usermod -u 1000 www-data

RUN a2enmod proxy proxy_fcgi rewrite ssl

COPY virtualhost.conf /etc/apache2/sites-enabled/000-default.conf
COPY virtualhost-ssl.conf /etc/apache2/sites-available/000-default-ssl.conf
COPY cert/server.key /etc/ssl/private/apache-selfsigned.key
COPY cert/server.crt /etc/ssl/certs/apache-selfsigned.crt

RUN a2ensite 000-default-ssl

# SURY
RUN apt-get update && \
    apt-get install -y apt-transport-https lsb-release ca-certificates wget
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list

ENV PHP_V="7.2"

RUN apt-get update && \
    apt-get install -y php$PHP_V \
                       php${PHP_V}-mysql \
                       php${PHP_V}-xml \
                       php${PHP_V}-intl \
                       php${PHP_V}-xdebug
# end SURY

RUN echo "xdebug.remote_connect_back=1" >> /etc/php/${PHP_V}/apache2/conf.d/20-xdebug.ini
RUN echo "xdebug.remote_enable=1" >> /etc/php/${PHP_V}/apache2/conf.d/20-xdebug.ini
RUN echo "xdebug.remote_log=/tmp/xdebug.log" >> /etc/php/${PHP_V}/apache2/conf.d/20-xdebug.ini
RUN echo "xdebug.remote_autostart=1" >> /etc/php/${PHP_V}/apache2/conf.d/20-xdebug.ini

WORKDIR /var/www

CMD apachectl -D FOREGROUND
