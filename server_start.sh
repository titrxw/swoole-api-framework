#! /bin/bash
path='/var/www/php/swoole-api-framework/index.php'
redisServer='/usr/bin/redis-server'
redisConf='/etc/redis/redis.conf'
stop() {
  #https://www.cnblogs.com/qianjinyan/p/9244746.html
  #grep -v grep | grep -v tail
  #1、第一部分 “grep -v grep" 在文档中过滤掉包含有grep字符的行
  #2、第二部分“grep -v tail” 在第一部分过滤掉之后再过滤掉剩余文档中包含有tail字符的行
  #3、总结一下就是：这条命令的意思就是过滤掉文档中包含字符“grep”和“tail”的行
  #4、可简化为：grep -v "cp|mkdir"
  #awk '{print $2}'  获取ps结果的第二列数据 https://www.cnblogs.com/losbyday/p/5854707.html
  ID=`ps -ef | grep redis-server | grep -v "grep" | awk '{print $2}'`
  for id in $ID
  do
    kill -9 $id
  done
  php $path stop
}
start() {
  `$redisServer $redisConf`
  php $path start $1
}
restart() {
  echo $2
  stop
  if [ "$1" = 'd' ]; then
    start &
    else
    start
  fi
}
case $1 in
'start')
  if [ "$2" = 'd' ]; then
    start &
    else
    start
  fi
  ;;
'stop')
  stop
  ;;
'restart')
  restart $2
  ;;
*)
  echo 'please user start|stop|start if need daemonize please add arg d'
  ;;
esac