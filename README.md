# Installation

## Prepare docker

Install docker
```sh
apt update
apt install docker.io docker-compose
```

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
After the build, start new container
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

Create DB scheme
```sh
php bin/console doctrine:scheme:create
```


# Making user admin
```sql
UPDATE `user`
SET `roles` = 'roles: a:1:{i:0;s:10:"ROLE_ADMIN";}'
WHERE @some_condition@
```
