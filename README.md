# 湘大点评后端及后台

## 部署
* 使用docker-composer管理容器
* 构建条件：  
  *   安装composer、php && php > php7.2
* 构建
  ```
  sudo chmod a+x install.sh && ./install.sh
  ```
* web访问端口映射在10303，部署前确保该端口未被占用
* mysql存在默认密码，做相应修改
* laravel.env需要单独配置
* laravel数据迁移未完成，需要手动操作 ps:之后做自动迁移

## [接口文档](https://git.sky31.com/dinghaodong/UpickBackend/blob/master/api.md)
> 暂时没有
