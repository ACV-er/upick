#!/bin/bash

cp .env.example .env
docker run --rm -it -v $PWD:/app composer:1.9.1 install

# 删除之前的sql文件
sudo rm -rf dockercnf/mysql5.7/db_data/*

# 删除之前的容器 运行docker-compose构建项目,并打开
docker-compose stop
docker-compose rm
docker-compose up --build -d

docker exec -it upick_php chown :www-data -R ./
docker exec -it upick_php chmod g+w -R ./
sleep 5
docker exec -it upick_php php artisan key:generate
docker exec -it upick_php php artisan migrate
