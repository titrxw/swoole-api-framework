<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-8-26
 * Time: 下午8:55
 */

define('APP_ROOT', dirname(dirname(__FILE__)));
if(!defined('APP_NAME'))
    define('APP_NAME','application');
include __DIR__.'/autoloader.php';


if (file_exists(APP_ROOT. '/vendor/autoload.php')) {
    define('COMPOSER', true);
    require_once (APP_ROOT. '/vendor/autoload.php');
} else {
    define('COMPOSER', false);
}


$conf = array(
    'default' => require_once __DIR__.'/conf/base.php',
    'app' => require_once APP_ROOT. '/' .APP_NAME.'/conf/conf.php'
);
\framework\web\Application::run($conf);
unset( $conf);
