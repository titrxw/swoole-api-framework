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
            'debug' => true,
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
            'type' => 'http'
        )
    )
);

