#!/bin/bash

cp .env.example .env
docker run --rm -it -v $PWD:/app composer:1.9.1 install

# 运行docker-compose构建项目,并打开
docker-compose up --build -d

docker exec -it upick_php chown :www-data -R ./
docker exec -it upick_php chmod g+w -R ./
docker exec -it upick_php php artisan key:generate
docker exec -it upick_php php artisan migrate
