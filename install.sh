#!/bin/bash

cp .env.example .env
composer install
php artisan key:generate

# 运行docker-compose构建项目,并打开
docker-compose up --build -d

docker exec -it upick_php chown :www-data -R ./
docker exec -it upick_php chmod g+w -R ./