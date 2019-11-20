#!/bin/bash

./tools/composer install
if [ $? -ne 0 ]; then
    echo "composer install失败"
    exit

#项目需要的用户组
group=www-data

#用户组不存在则创建
egrep "^$group" /etc/group >& /dev/null
if [ $? -ne 0 ]
then
    groupadd $group
fi

# 赋予用户组内用户写权限
sudo chown acver:www-data -R ./
sudo chmod g+w -R ./

# 运行docker-compose构建项目,并打开
docker-compose up -d