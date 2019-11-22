#!/bin/bash


docker run --rm -it -v $PWD:/app composer:1.9.1 install

# 删除之前的sql文件,上线部署后不执行该步骤
sudo rm -rf dockercnf/mysql5.7/db_data/*

# 删除之前的容器 运行docker-compose构建项目,并打开
docker-compose stop
docker-compose rm
docker-compose up --build -d
if [ ! -f ".env" ]; then
  sleep 5
  cp .env.example .env
  docker exec -it upick_php php artisan key:generate
fi

docker exec -it upick_php php artisan migrate
docker exec -it upick_php chown :www-data -R ./
docker exec -it upick_php chmod g+w -R ./

# 等待mysql初始化完成？或许是别的应用，不等的话没法正常数据迁移

