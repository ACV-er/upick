#!/bin/bash

# 第一次安装才执行该脚本
if [ ! -f ".env" ]; then
  sudo rm -rf ./dockercnf/mysql5.7/db_data/*
  docker run --rm -it -v $PWD:/app composer:1.9.1 install
  docker-compose up --build -d

  sleep 2
  cp .env.example .env
  # 删除之前的sql文件,上线部署后不执行该步骤

  docker exec -it upick_php php artisan key:generate
  docker exec -it upick_php php artisan storage:link

  docker exec -it upick_php php artisan migrate:refresh --seed
  docker exec -it upick_php chown :www-data -R ./
  docker exec -it upick_php chmod g+w -R ./
fi

