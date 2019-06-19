# Installation

## Docker setup

* [Install docker](https://docs.docker.com/install/)
* [Docker post install steps](https://docs.docker.com/install/linux/linux-postinstall/) - optional

## Prepare components

Create docker image
```sh
bash run.sh
```

Attaching to the `web` container
```sh
docker exec -it track_web_1 bash
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


# Making user admin
```sql
UPDATE `user`
SET `roles` = 'roles: a:1:{i:0;s:10:"ROLE_ADMIN";}'
WHERE @some_condition@
```

# Using facebook login
Make sure that you are using the facebook token from `.env.dist`

You have to manually set [hostname](https://linux.die.net/man/1/hostname) `gps.test` to match your container ip. Then you must access the project via gps.test instead of the container IP
> if you need different hostname - please create an issue.

Example config in `/etc/hosts`
```
172.20.0.3 gps.test
```
