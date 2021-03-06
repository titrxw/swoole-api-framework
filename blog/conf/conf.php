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
        },
        'meedo' => function (array $params) {
            return new \Medoo\Medoo($params);      //这里测试composer的加载
        },
        'smarty' => function (array $params) {
            include_file("/blog/lib/libs/Autoloader.php"); //包含smarty类文件  
            include_file("/blog/lib/libs/Smarty.class.php"); //包含smarty类文件  
            $smarty = new Smarty(); //建立smarty实例对象$smarty   
            $smarty->compile_dir = APP_ROOT . 'blog/runtime/templates_c/'; //设置模板目录 ——这里的文件很重要的，需要写的模板文件  
            $smarty->compile_dir = APP_ROOT . 'blog/runtime/templates_c/';; //设置编译目录 ——混编文件，自动生成  
            $smarty->cache_dir = APP_ROOT . 'blog/runtime/cache/'; //缓存目录   
            $smarty->cache_lifetime = 0; //缓存时间   
            $smarty->caching = true; //缓存方式   
            $smarty->left_delimiter = "{";   
            $smarty->right_delimiter = "}";
            return $smarty;
        }
    ),
    'addComponentsMap' => array(
        'validate' => 'framework\\components\\validate\\Validate',
        'password' => 'framework\\components\\security\\Password',
        'tokenBucket' => 'framework\\tokenbucket\\Bucket',
        'apireset' => 'blog\\lib\\ApiReset',
        'imgzip' => 'framework\\components\\imagic\\Imgzip',
        //'page' => 'framework\\components\\page\\Page',//如果使用api的话这里不需要
        //'view' => 'framework\\components\\view\\View',     //如果使用api的话这里不需要,
        'upload' => 'framework\\components\\upload\\NUpload',
        //'msgTask' => 'blog\\conf\\Task',
        //'crontabTask' => 'blog\\conf\\CrontabTask',
        'redis' => 'framework\\components\\cache\\Redis',
        'uniqueid' => 'framework\\components\\uniqueid\\UniqueId',
//        'sessionRedis' => 'framework\\components\\cache\\Redis',
//        'session' => 'framework\\components\\session\\Session',
        'captcha' => 'framework\\components\\captcha\\Captcha'
    ), //该项因为设计上的问题暂时不添加
    'components' => array(
        'controller' => [
            'controller' => [
                'prefix' => '',
                'suffix' => ''
            ],
            'action' => [
                'prefix' => '',
                'suffix' => 'Api'
            ]
        ],
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
            ),
            'prefx' => 'rxwyun_102410_ngf_'
        ),
        'captcha' => array(
            'height' => 70,
            'width' => 200,
            'num' => 5,
            'type' => 'png'   //png jpg gif
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
        ],
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
        'upload' => array(
            'maxSize' => 2088960
        ),
        'captcha' => array(
            'height' => 70,
            'width' => 200,
            'num' => 5,
            'type' => 'png',   //png jpg gif,
            'response' => 'response'
        ),
        'page' => array(
            'url' => 'url'
        ),
        'model' => array(
            'db' => 'meedo'
        ),
    )
);
