<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-8-26
 * Time: 下午8:55
 */
define('APP_ROOT', dirname(dirname(__FILE__)).'/');

date_default_timezone_set('PRC');

if(!defined('DEBUG'))
    define('DEBUG',true);
define('SYSTEM_APP_NAME', 'APP');

include __DIR__.'/autoloader.php';

if (file_exists(APP_ROOT. 'vendor/autoload.php')) {
    define('COMPOSER', true);
    require_once (APP_ROOT. 'vendor/autoload.php');
} else {
    define('COMPOSER', false);
}
$conf = array(
    'default' => require_once __DIR__.'/conf/base.php',
    'app' => []
);


$server = new \framework\server\Server();

\framework\web\Application::run($conf, $argv[1] ?? 'start');
unset($conf);
