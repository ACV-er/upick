# 湘大点评后端及后台

## 部署
* 使用docker-composer管理容器
* 构建条件：  
  *   docker
  *   docker-compose
  *   npm [非必须，生成文档时使用。不装也必须写好apidoc注释]
* 构建
  ```
  ./install.sh
  ```
* web访问端口映射在10303，部署前确保该端口未被占用
* mysql存在默认密码，做相应修改**\[必须修改\]**
* laravel.env需要单独配置
* phpMyAdmin 地址为 `/mathjucool` (数据库)

## [接口文档](https://git.sky31.com/dinghaodong/UpickBackend/blob/master/api.md)
> 暂时没有
