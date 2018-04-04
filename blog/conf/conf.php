<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-8-26
 * Time: 下午8:50
 */
return array(
    'composer' => array(
//        'Logger' => function (array $params) {
//            return new \Monolog\Logger($params[0]);      //这里测试composer的加载
//        },
    'crawler' => function ($params) {
        return new Symfony\Component\DomCrawler\Crawler();
    }
       
    ),
    'addComponentsMap' => array(
        'validate' => 'framework\\components\\validate\\Validate',
        'password' => 'framework\\components\\security\\Password',
        'tokenBucket' => 'framework\\tokenbucket\\Bucket',
        'apireset' => 'blog\\lib\\ApiReset',
        'imageZip' => 'blog\\lib\\ImageZip',
        //'page' => 'framework\\components\\page\\Page',//如果使用api的话这里不需要
        //'view' => 'framework\\components\\view\\View',     //如果使用api的话这里不需要,
        'upload' => 'framework\\components\\upload\\NUpload',
        //'msgTask' => 'blog\\conf\\Task',
        //'crontabTask' => 'blog\\conf\\CrontabTask',
        'redis' => 'framework\\components\\cache\\Redis',
//        'sessionRedis' => 'framework\\components\\cache\\Redis',
//        'session' => 'framework\\components\\session\\Session',
        //'captcha' => 'framework\\components\\captcha\\Captcha',
        //'crontab' => 'framework\\crontab\\Crontab'
    ), //该项因为设计上的问题暂时不添加
    'components' => array(
        'redis' => array(
            'host'         => '127.0.0.1', // redis主机
            'port'         => 6379, // redis端口
            'password'     => '', // 密码
            'select'       => 0, // 操作库
            'expire'       => 3600, // 有效期(秒)
            'timeout'      => 0, // 超时时间(秒)
            'persistent'   => false, // 是否长连接,
            'prefix' => ''
        ),
        'apireset' => array(
            'key' => '0a4df5rge6t6h8beg32g4',
            'step' => 5
        ),
//        'sessionRedis' => array(
//            'host'         => '127.0.0.1', // redis主机
//            'port'         => 6379, // redis端口
//            'password'     => '', // 密码
//            'select'       => 0, // 操作库
//            'expire'       => 3600, // 有效期(秒)
//            'timeout'      => 0, // 超时时间(秒)
//            'persistent'   => true, // 是否长连接,
//            'prefix' => ''
//        ),
//        'db' => array(
//            'db' => array(
//                'db1' => array(
//                    'type' => 'mysql',
//                    'dbName' => 'test',
//                    'user' => 'root',
//                    'password' => '123456',
//                    'host' => 'localhost:3306',
//                    'persistent' => true
//                )
//            )
//        ),
//        'session' => array(
//              'cookie' => 'cookie',
//            'redis' => array(
//                'session_name' => '', // sessionkey前缀
//            ),
//            'httpOnly'=> true,
//            'driver'=> array(
//                'type' => 'redis',
//                'name' => 'sessionRedis'
//            ),
//            'path'=> '',
//            'name' => 'EASYSESSION',
//            'prefix' => 'easy-'
//        ),
        'redis' => array(
            'host'         => '127.0.0.1', // redis主机
            'port'         => 6379, // redis端口
            'password'     => '', // 密码
            'select'       => 0, // 操作库
            'expire'       => 3600, // 有效期(秒)
            'timeout'      => 0, // 超时时间(秒)
            'persistent'   => false, // 是否长连接,
            'prefix' => ''
        ),
        'upload' => array(
            'accept' => array(
                'jpg',
                'png'
            )
        ),
        'captcha' => array(
            'height' => 70,
            'width' => 200,
            'num' => 5,
            'type' => 'png'   //png jpg gif
        ),
        'crontab' => array(
            'tasks' => array(
                '* * * 7-12/1 *--crontabTask test',
                '2 /2 3-8 3,5,8 1--crontabTask test',
                '3 /2 3-8/2 3,5 1--crontabTask test'
            )
        ),
        'meedo' => array(
            'database_type' => 'mysql',
            'database_name' => 'blog',
            'server' => '127.0.0.1',
            'username' => 'root',
            'password' => '123456',
            // [optional]
            'charset' => 'utf8',
            'port' => 3306,
            // [optional] Table prefix
            'prefix' => 'blog_',
        
            // [optional] Enable logging (Logging is disabled by default for better performance)
            'logging' => true,
        ),
        'server' => array(
            'event' => 'application\\conf\\ServerWebSocketEvent',
            'ip' => '127.0.0.1',
            'port' => '85',
            'supportHttp' => false,
            'type' => 'http',
            'factory_mode'=>2,
            'dispatch_mode' => 2,
            'task_worker_num' => 2, //异步任务进程
            "task_max_request"=>10,
            'max_request'=>3000,
            'worker_num'=>1,
            'task_ipc_mode' => 2,
            'log_file' => '/tmp/swoole.log',
            'enable_static_handler' => true,
            'document_root' => '/var/www/php/easy-framework-swoole/public/assets/application/images/' //访问链接是 127.0.0.1:81/jpg文件名
        ),
        'dispatcher' => array(
            'action' => array(
                'prefix' => '',
                'suffix' => 'Api'
            )
        ),
        'tokenBucket' => [
            'buckets' => [
                'request' => [
                    'class' => 'framework\\tokenbucket\\Request',
                    'conf' => [
                        'max' => 2000,
                        'key' => 'bucket_request',
                        'range' => 2000 * 60, //单位s
                        'addStep' => 0,
                        'timeStep' => 3//单位s
                    ]
                ]
            ],
        ]
    )
);