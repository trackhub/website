#!/bin/bash

set -e

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
    export TRACK_SEEDER_TRACK_COUNT=4 &&
    export PLACE_SEEDER_PLACE_COUNT=5 &&
    cd script/migration && ./vendor/bin/phinx seed:run
"
