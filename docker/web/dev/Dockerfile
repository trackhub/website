FROM trackhub-web

RUN a2ensite 000-default-ssl

COPY cert/server.key /etc/ssl/private/apache.key
COPY cert/server.crt /etc/ssl/certs/apache.crt

ENV PHP_V="7.3"

RUN apt-get update && \
    apt-get install -y php${PHP_V}-xdebug

COPY xdebug.ini /etc/php/${PHP_V}/apache2/conf.d/20-xdebug.ini
COPY xdebug.ini /etc/php/${PHP_V}/cli/conf.d/20-xdebug.ini

CMD usermod -u $WEB_UID www-data && \
    su www-data bash -c "cd /var/www/script/migration && composer install --no-dev && ./vendor/bin/phinx migrate" && \
    su www-data bash -c "composer install -d /var/www" && \
    su www-data -s /bin/bash -c "cd /var/www && yarn install" && \
    su www-data -s /bin/bash -c "cd /var/www && yarn encore dev" && \
    apachectl -D FOREGROUND
