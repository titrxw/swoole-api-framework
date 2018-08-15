# 自动重启脚本

* 使用`inotify`监听PHP源码目录
* 程序文件更新时自动`reload`服务器程序

运行脚本
----
依赖`inotify`和`swoole`扩展
```shell
pecl install swoole
pecl install inotify
php daemon.php
```

运行程序
```php
require __DIR__.'/src/Swoole/ToolKit/AutoReload.php';

//设置服务器程序的PID
$kit = new Swoole\ToolKit\AutoReload(2914);
//设置要监听的源码目录
$kit->watch(__DIR__.'/tests');
//监听后缀为.php的文件
$kit->addFileType('.php');
$kit->run();

```
