FROM trackhub-web
ENV PHP_V="7.3"

RUN apt-get update && \
    apt-get install -y php${PHP_V}-xdebug

COPY xdebug.ini /etc/php/${PHP_V}/apache2/conf.d/20-xdebug.ini
COPY xdebug.ini /etc/php/${PHP_V}/cli/conf.d/20-xdebug.ini
