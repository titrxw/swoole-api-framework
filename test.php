<?php
use Swoole\Coroutine as co;
 
// 协程
$time = microtime(true);
// 创建10个协程
for($i = 0; $i < 10; ++$i)
{
    // 创建协程
    go(function() use($i){
          $j = 0;
          co::sleep(1.0);
        echo $i, PHP_EOL;
    });
}
echo '34';
for($i = 10; $i < 20; ++$i)
{
    // 创建协程
        go(function() use($i){
          $j = 0;
          co::sleep(1.0);
        echo $i, PHP_EOL;
    });
}
swoole_event_wait();
echo 'co time:', microtime(true) - $time, ' s', PHP_EOL;
 
// 同步
$time = microtime(true);
// 创建10个协程
for($i = 0; $i < 10; ++$i)
{
    sleep(1); // 模拟请求接口、读写文件等I/O
    echo $i, PHP_EOL;
}
echo 'sync time:', microtime(true) - $time, ' s', PHP_EOL;