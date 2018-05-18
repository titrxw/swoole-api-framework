<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-8-26
 * Time: 下午8:55
 */
define('APP_ROOT', dirname(dirname(__FILE__)).'/');

require __DIR__ . '/base/function.php';
include __DIR__.'/autoloader.php';

\framework\ServerApplication::run($argv[1] ?? 'start');
