FROM trackhub-web

CMD su www-data bash -c "export APP_ENV=prod && composer install -d /var/www --no-dev -o" && \
    su www-data -s /bin/bash -c "cd /var/www && yarn install" && \
    su www-data -s /bin/bash -c "cd /var/www && yarn encore production" && \
    usermod -u $WEB_UID www-data && \
    apachectl -D FOREGROUND
