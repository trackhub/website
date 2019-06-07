# Installation

## Prepare docker

[Install docker](https://docs.docker.com/install/)

Add user to docker group
```sh
sudo usermod -aG docker $USER
su - $USER
```

## Prepare components

Create docker image
```sh
bash run.sh
```
After the build, attach to **web** container
```sh
docker-compose -p track exec web bash
```

Install web components
```sh
composer install
yarn install
# dev env
yarn encore dev
# prod env
yarn encore production
```

Create DB schema
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
