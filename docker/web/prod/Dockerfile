FROM trackhub-web

CMD usermod -u $WEB_UID www-data && \ &&
    su www-data bash -c "export APP_ENV=prod && composer install -d /var/www --no-dev -o" && \
    su www-data -s /bin/bash -c "cd /var/www && yarn install" && \
    su www-data -s /bin/bash -c "cd /var/www && yarn encore production" && \
    apachectl -D FOREGROUND
