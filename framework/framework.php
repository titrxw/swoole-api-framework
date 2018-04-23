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

//$dis = new \framework\conformancehash\Dispatcher();
////
//$dis->addNode('192.168.1.100', '90');
//$dis->addNode('192.118.2.100', '90');
//$dis->addNode('192.128.3.100', '90');
//$dis->addNode('192.128.2.100', '90');
//$dis->addVirtualNode();
//$dis->removeNode('192.168.1.100', '90');
//var_dump($dis->_list->_nodeList);
//$dis->findNextNodeByValue('dstrsffyhjhgfjghdjhfjdfdf');
//$dis->findNextNodeByValue('dstsdfdf');

\framework\web\Application::run($argv[1] ?? 'start');
