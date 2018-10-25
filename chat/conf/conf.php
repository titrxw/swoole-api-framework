<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-8-26
 * Time: 下午8:50
 */
return array(
    'composer' => array(
        'meedo' => function (array $params) {
            return new \Medoo\Medoo($params);      //这里测试composer的加载
        }
    ),
    'addComponentsMap' => array(
        'validate' => 'framework\\components\\validate\\Validate',
        'password' => 'framework\\components\\security\\Password',
        'tokenBucket' => 'framework\\tokenbucket\\Bucket',
        'apireset' => 'blog\\lib\\ApiReset',
        'redis' => 'framework\\components\\cache\\Redis',
        'uniqueid' => 'framework\\components\\uniqueid\\UniqueId',
        // 'wsevent' => 'chat\\conf\\WsEvent'
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
                    'auto' => true,
                    'conf' => [
                        'max' => 20,
                        'key' => 'bucket_b_request',
                        'range' => 60, //单位s
                        'addStep' => 0,
                        'timeStep' => 3//单位s
                    ]
                ],
                'mobile' => [
                    'class' => 'framework\\tokenbucket\\Mobile',
                    'auto' => false,
                    'conf' => [
                        'max' => 5,
                        'key' => 'mobile',
                        'range' => 60, //单位s
                        'addStep' => 0,
                        'timeStep' => 3//单位s
                    ]
                ],
                'ip' => [
                    'class' => 'framework\\tokenbucket\\Ip',
                    'auto' => false,
                    'conf' => [
                        'max' => 3,
                        'key' => 'ip',
                        'range' => 5, //单位s
                        'addStep' => 0,
                        'timeStep' => 3//单位s
                    ]
                ]
            ],
        ]
    )
);
