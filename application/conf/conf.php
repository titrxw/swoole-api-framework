<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-8-26
 * Time: 下午8:50
 */
return array(
    'composer' => array(
        'Logger' => function (array $params) {
            return new \Monolog\Logger($params[0]);      //这里测试composer的加载
        }
    ),
    'addComponentsMap' => array(
        'validate' => 'framework\\components\\validate\\Validate',
        'page' => 'framework\\components\\page\\Page',//如果使用api的话这里不需要
        'view' => 'framework\\components\\view\\View'     //如果使用api的话这里不需要
    ), //该项因为设计上的问题暂时不添加
    'components' => array(
        'cache' => array(
            'host'         => '127.0.0.1', // redis主机
            'port'         => 6379, // redis端口
            'password'     => '', // 密码
            'select'       => 0, // 操作库
            'expire'       => 3600, // 有效期(秒)
            'timeout'      => 0, // 超时时间(秒)
            'persistent'   => true, // 是否长连接,
            'prefix' => ''
        ),
        'Pdo' => array(
            'db' => array(
                'db1' => array(
                    'type' => 'mysql',
                    'dbName' => 'test',
                    'user' => 'root',
                    'password' => '123456',
                    'host' => 'localhost:3306',
                    'persistent' => true
                )
            )
        ),
        'server' => array(
            'event' => 'application\\conf\\ServerEvent',
            'ip' => '127.0.0.1',
            'port' => '81',
            'task_worker_num' => 4, //异步任务进程
            "task_max_request"=>10,
            'max_request'=>3000,
            'worker_num'=>4
        )
    )
);