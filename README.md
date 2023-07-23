# game-api

### 拉取代码

1. 拉取代码
```bash
## docker环境

## 接口代码
git clone git@github.com /data/www/admin_api

```

2. 修改配置

```bash
## Todo
```

3. 启动docker
```bash
cd /data/docker_compose/web
chmod +x /data/docker_compose/shell/*.sh
docker-compose build
docker-compose up -d
docker-compose logs
```
### 初始化

```bash
docker exec -it GAME_php82 sh
cd /www/admin_api/
## 初始化目录
php application/bin/install.php
## 修改配置文件
cp .env.test .env
vi .env 
## 修改ENVIRON的环境变量
