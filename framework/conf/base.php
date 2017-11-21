<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-8-26
 * Time: 下午9:33
 */
return array(
    'composer' => array(
    ),
    'components' => array(
        'log' => array(
            'path' => 'runtime/log/',
            'isLog' => true,
            'maxSize' => 2097152
        ),
        'url' => array(
            'routerKey' => '',
            'type' => '/',
            'separator' => '/',
            'defaultController' => 'index',
            'defaultAction' => 'index'
        ),
        'dispatcher' => array(
            'controller' => array(
                'prefix' => '',
                'suffix' => ''
            ),
            'action' => array(
                'prefix' => '',
                'suffix' => 'Action'
            )
        ),
        'resquest' => array(
            'separator' => '/',
        ),
        'response' => array(
            'defaultType' => 'text',
            'charset' => 'utf-8'
        ),
        'view' => array(
            'templatePath' => 'view',
            'cachePath' => 'runtime/viewCache',
            'compilePath' => 'runtime/compile',
            'viewExt' => '.html',
            'isCache' => false,
            'cacheExpire' => 3600,
            'leftDelimiter' => '{',
            'rightDelimiter' => '}'
        ),
        'server' => array(
            'type' => 'http',
            'factory_mode'=>2,
            'dispatch_mode' => 2,
            'task_worker_num' => 2, //异步任务进程
            "task_max_request"=>10,
            'max_request'=>3000,
            'worker_num'=>3,
            'task_ipc_mode' => 2,
            'log_file' => '/tmp/swoole.log',
            'enable_static_handler' => true,
            'document_root' => '/var/www/php/easy-framework-swoole/public/assets/application/images/' //访问链接是 127.0.0.1:81/jpg文件名
        ),
        'upload' => array(
            'maxSize' => 2088960
        )
    )
);

