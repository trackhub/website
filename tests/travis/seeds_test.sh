#!/bin/bash

set -e

set -x

bash ./run.sh &
timeout --foreground 360 bash -c "
  until docker-compose -p track exec web curl localhost
  do
      echo 'Waiting for web server...'
      sleep 3
  done
"

echo "Web server is UP!"

docker exec -it $(docker ps --format '{{.Names}}' | grep "web") /bin/bash -c "
    cd script/migration && ./vendor/bin/phinx seed:run
"
