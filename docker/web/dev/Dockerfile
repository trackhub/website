FROM trackhub-web

RUN a2ensite 000-default-ssl

COPY cert/server.key /etc/ssl/private/apache.key
COPY cert/server.crt /etc/ssl/certs/apache.crt

ENV PHP_V="7.4"

RUN apt-get update && \
    apt-get install -y php${PHP_V}-xdebug

COPY xdebug.ini /etc/php/${PHP_V}/apache2/conf.d/20-xdebug.ini
COPY xdebug.ini /etc/php/${PHP_V}/cli/conf.d/20-xdebug.ini

COPY entrypoint.sh /entrypoint-dev.sh

CMD bash /entrypoint-dev.sh
