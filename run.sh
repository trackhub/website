#!/bin/bash

set -e

# If the script is run with sudo, UID is 0. This is an issue when running
# "usermod -u $WEB_UID www-data" in the web container.
# In this case assign WEB_UID to 1000
[[ $UID == 0 ]] && export WEB_UID=1000 || export WEB_UID=$UID

docker build --tag=trackhub-web ./docker/web/
docker-compose -p track build

if [[ "$1" == "-p" ]]; then
    docker-compose -p track -f docker-compose.yml -f docker-compose-prod.yml build
    docker-compose -p track -f docker-compose.yml -f docker-compose-prod.yml up
else
    docker-compose -p track up
fi
