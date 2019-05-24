# Installation
```
composer install
yarn install
# dev env
yarn encore dev
# prod env
yarn encore production
```

# Making user admin
```sql
UPDATE `user`
SET `roles` = 'roles: a:1:{i:0;s:10:"ROLE_ADMIN";}'
WHERE @some_condition@
```
