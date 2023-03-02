# Slow Admin

***
基于 `webman` 、 `slow-admin` 、 `amis` 开发的后台框架.

### webman

webman是一款基于workerman开发的高性能HTTP服务框架。webman用于替代传统的php-fpm架构，提供超高性能可扩展的HTTP服务。你可以用webman开发网站，也可以开发HTTP接口或者微服务。
除此之外，webman还支持自定义进程，可以做workerman能做的任何事情，例如websocket服务、物联网、游戏、TCP服务、UDP服务、unix socket服务等等。

### slow-admin

基于 Laravel 、 amis 开发的后台框架. 快速且灵活~

### amis

amis 是一个低代码前端框架，它使用 JSON 配置来生成页面，可以减少页面开发工作量，极大提升效率。

## 特点: 快速且灵活

- 基于 amis 以 json 的方式构建页面，减少前端开发工作量，提升开发效率。
- 在 amis 100多个组件都不满足的情况下, 可自行开发前端。
- 框架为前后端分离 (不用再因为框架而束手束脚~)。
- 前端基于 Soybean Admin 使用最新流行技术栈(Vue3、Vite3、TS、NaiveUI和UnoCSS等)。

## 文档

***

- [《Webman》](https://www.workerman.net/doc/webman/)
- [《amis》](https://aisuda.bce.baidu.com/amis/zh-CN/docs/index)
- [《Slow Admin》](https://slowlyo.gitee.io/slow-admin-doc)

## 功能

***

- 基础后台功能
    - 后台用户管理
    - 角色管理
    - 权限管理
    - 菜单管理
- **代码生成器**
    - 创建数据迁移文件
    - 创建数据表
    - 创建模型
    - 创建基础控制器代码
    - 创建Service
- `Amis` 全组件封装

## demo
[Slow Admin Demo 地址](http://admin-demo.slowlyo.top)

## 截图

## 环境

***

- PHP >= 8.0
- Webman >= 1.5

## 一分钟跑起来

***

1. 安装

```php
composer create-project wowthree/slow-webman example-app
```

2. 配置 `config/database`

3. 数据库迁移文件

> php vendor/bin/phinx migrate -e dev

3. 运行项目

> 在你的环境把代码跑起来 <br>
> `php window.php / php webman start` <br>
> 在浏览器打开 `http://127.0.0.1:8787/admin` 即可访问 <br>
> 初始账号密码都是 `admin`

## 鸣谢

***

- [webman](https://www.workerman.net/webman)
- [slow-admin](https://slowlyo.gitee.io/slow-admin-doc)
- [Soybean Admin](https://github.com/honghuangdc/soybean-admin)
- [amis](https://github.com/baidu/amis)
- [Laravel-admin](https://www.laravel-admin.org/)
- [Dcat Admin](https://github.com/jqhph/dcat-admin)
- [Amis Admin](https://github.com/SmallRuralDog/amis-admin)
