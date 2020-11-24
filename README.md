# Installation

! MacOS/Windows is not supported !

Our local environment works only on Linux based OS. if you are able to modify the enviroment to be compatible with windows/macos please create MR containing CI tests. 

## Docker setup

* [Install docker](https://docs.docker.com/install/)
* [Docker post install steps](https://docs.docker.com/install/linux/linux-postinstall/) - optional

## Container build/start

Start local env
```sh
bash run.sh
```

Attaching to the `web` container
```sh
docker exec -it -u www-data track_web_1 bash
```

### Install web components (done automatically on container startup)
```sh
composer install
yarn install
# dev env
yarn encore dev
# prod env
yarn encore production
```

### Create DB schema (done automatically on container startup)
```sh
cd /var/www/script/migration
composer install --no-dev
./vendor/bin/phinx migrate
```


# Promoting user to admin
Execute the following sql query, replace `@some_condition@` with your conditions.

```sql
UPDATE `user`
SET `roles` = 'a:1:{i:0;s:10:"ROLE_ADMIN";}'
WHERE @some_condition@
```

# Facebook login
1. In order to use *our development application*, we need to manually add you as facebook app developer, please create an issue.
2. Make sure you are using the facebook token from `.env.dist`
3. You have to manually set [hostname](https://linux.die.net/man/1/hostname) `gps.test` to match your container ip. Then you must access the project via https://gps.test instead of the container IP. if you need different hostname - please create an issue/PR.

Example config in `/etc/hosts`
```
172.20.0.3 gps.test
```

If you use your own facebook application, then you have to fill credentials in `.env` file.

# GPS info

# GPS elevation accuracy
Our ElevationDiffReducer have "buffer" of 40 metres.
Elevation accuracy:
* 121m - according to [Garmin](https://support.garmin.com/en-GB/?faq=QPc5x3ZFUv1QyoxITW2vZ6)
* 31m - according to [Singletrack](https://www.singletracks.com/gps/gps-elevation-accuracy-test-smartphone-apps-vs-dedicated-gps/)
* 10m - according to [gpsinformation.net](http://gpsinformation.net/main/altitude.htm)
