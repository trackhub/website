#!/bin/bash

set -e

export WEB_UID=$UID

docker-compose build
docker-compose up
