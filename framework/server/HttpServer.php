<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-9-16
 * Time: 下午8:48
 */
namespace framework\server;

use framework\base\Container;

class HttpServer extends BaseServer
{
    protected function init()
    {
        $hasOnRequest = false;
        if (!$this->_server) {
            $hasOnRequest = true;
            $this->_server = new \swoole_http_server($this->_conf['ip'], $this->_conf['port']);
        }

        parent::init(); // TODO: Change the autogenerated stub

        if ($hasOnRequest) {
            $this->onRequest();
        }
    }

    protected function execApp(&$response)
    {
        // TODO: Implement execApp() method.
        $container = Container::getInstance();
        $urlInfo = $container->getComponent(SYSTEM_APP_NAME, 'url')->run();
        $_SERVER['CURRENT_SYSTEM'] = $urlInfo['system'];
        $result = '';

        if ($urlInfo !== false) {
            // 初始化配置项
            if (!$container->appHasComponents($urlInfo['system'])) {
//                这里现在还缺少文件系统
                $appConf = require_file($urlInfo['system'] . '/conf/conf.php');
                $container->addComponents($urlInfo['system'], $appConf['addComponentsMap'] ?? []);
                $container->setAppComponents($urlInfo['system'] ,array(
                    'components' => $appConf['components'] ?? [],
                    'composer' => $appConf['composer'] ?? []
                ));
                unset($appConf);
            }

            $result = $container->getComponent(SYSTEM_APP_NAME, 'dispatcher')->run($urlInfo);
        }

        unset($container);
        return $result;
    }

    protected function onRequest()
    {
        $this->_server->on("request", function (\swoole_http_request $request,\swoole_http_response $response)
        {
            if (DEBUG)
            {
                ob_start();
            }
            if ($this->_event)
            {
                $this->_event->onRequest($request,$response);
            }
            $container = Container::getInstance();
            if (!empty($request->get)) {
                $_GET = $request->get;
            }
            if (!empty($request->post)) {
                $_POST = $request->post;
            }
            if (!empty($request->files)) {
                $_FILES = $request->files;
//                $container->getComponent('upload')->save('file'); 上传文件测试
            }
            if (!empty($request->cookie)) {
                $_COOKIE = $request->cookie;
            }

            $hasEnd = false;
            try
            {
                if ($this->_event)
                {
                    $this->_event->onResponse($request,$response);
                }
                $request->server['HTTP_HOST'] = $request->header['host'];
                foreach ($request->server as $key => $item)
                {
                    $request->server[strtoupper($key)] = $item;
                    unset($request->server[$key]);
                }
                $_SERVER = $request->server;


                $result = $this->execApp($response);
                $container->getComponent(SYSTEM_APP_NAME, 'cookie')->send($response);
                if (DEBUG)
                {
                    $elseContent = ob_get_clean();
                    if ($elseContent) {
                        if (is_array($elseContent)) {
                            $elseContent = json_encode($elseContent);
                        }
                        $container->getComponent(SYSTEM_APP_NAME, 'response')->send($response, $elseContent);
                        unset($elseContent);
                    }
                }
                $hasEnd = $container->getComponent(SYSTEM_APP_NAME, 'response')->send($response, $result);
            }
            catch (\Throwable $exception)
            {
                $code = $exception->getCode() > 0 ? $exception->getCode() : 404;
                $container->getComponent(SYSTEM_APP_NAME, 'header')->setCode($code);
                $result = $exception->getMessage().$exception->getTraceAsString();
                if (DEBUG) {
                    $result .= ob_get_clean();
                }
                $container->getComponent(SYSTEM_APP_NAME, 'response')->send($response, $result );
                $this->handleThrowable($exception);
            }
            //  4.0版本后request回调本身是携程，但是如果处理请求中需要使用携程的话，可以如下使用
//             $id = \Swoole\Coroutine::getuid();
//             go (function () use ($id) {
//                 \Swoole\Coroutine::sleep(5.2);
//                 \Swoole\Coroutine::resume($id);
//             });
//             \Swoole\Coroutine::suspend();

            if (!$hasEnd) {
                $response->end();
            }
            $container->finish(\getModule());
            $container->finish(SYSTEM_APP_NAME);
            $_GET = [];
            $_POST = [];
            $_FILES = [];
            $_COOKIE = [];
            $_SERVER = [];
            unset($container,$request,$response, $urlInfo);
        });
    }
}
