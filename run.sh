#!/bin/bash

set -e

export WEB_UID=$UID

docker build --tag=trackhub-web ./docker/web/

docker-compose build
if [ "$1" == "-p" ]; then
    docker-compose -f docker-compose.yml -f docker-compose-prod.yml up
else
    docker-compose up
fi
