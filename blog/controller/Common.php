<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/8/27
 * Time: 20:58
 */
namespace blog\controller;
use blog\lib\Web;

class Common extends Web
{
    private $_userM;

    protected function afterInit()
    {
        $this->_userM = $this->model('User');
    }

    public function zookeeperSetApi()
    {
        // 这里是应用配置
        $this->zookeeper->getHandle()->set( '/blog/conf/test', json_encode([
            'path' => 'http://127.0.0.1/index.txt',           //配置文件的保存地址
            'version' => 1.7
        ]));
        // APP代表系统配置
        $this->zookeeper->getHandle()->set( '/APP/conf/test', json_encode([
            'path' => 'http://127.0.0.1/index.txt',
            'version' => 1.7
        ]));
    }

    public function zookeeperApi()
    {
        var_dump($this->zookeeper->get('test'));
    }

    public function goApi() {
        coroutine(function () {
            echo 3;
            \Swoole\Coroutine::sleep(5.2);
        });
        echo 1;
    }

    public function rpcApi()
    {
        $res = $this->rpcClient->call('127.0.0.1',8081,'HelloService','sayHello','24324');
        var_dump($res);
    }

    /**
     * @method get
     * 
     * @params string  $name 不能为空
     * @rule mobile|post|账号格式错误 regex|/^1[34578]\d{9}$/  
     * @rule password|post|密码格式错误 require
     * @rule sure_password|post|确认密码格式错误 require
     */
    public function test1Api()
    {
        // $this->cookie->set('rwar', 'dsfsdf');
        // return $this->_userM->test();
        var_dump(uniqueId());
//        $this->addTask('msgTask', 'sendMsg', array('mobile' => '1212121212'));
    }

    public function testApi()
    {
        $client = new \swoole_client(SWOOLE_SOCK_TCP);
        if (!$client->connect('127.0.0.1', 8082, -1))
        {
            echo ("connect failed. Error: {$client->errCode}\n");
        }
        $client->send('publish');
        $result = $client->recv();
        if ($result) {
            $client->send( \json_encode([
                '/1 * * * *--crontabTask test',
                '/1 * * * *--sendMsg sendMsg'
            ]));
        }
        $result = $client->recv();
        \var_dump($result);
        $this->addTask('msgTask', 'sendMsg', array('mobile' => '1212121212'));
        $this->seaslog->info('ewrwer');
        var_dump(\uniqueId());
    }

    public function loginApi ()
    {
        \var_dump(1);
        
        $mobile = $this->request->post('mobile');
        $password = $this->request->post('password');

        $result = $this->_userM->login($mobile, $password);
        if ($result) {
            return [200, $result];
        }

        return [501, '登录失败'];
    }

    /**
     * @method post
     * 
     * @params string  $name 不能为空
     * @rule mobile|post|账号格式错误 regex|/^1[34578]\d{9}$/  
     * @rule password|post|密码格式错误 require
     * @rule sure_password|post|确认密码格式错误 require
     */
    public function registerApi()
    {
        $mobile = $this->request->post('mobile');
        $password = $this->request->post('password');
        $sure_password = $this->request->post('sure_password');
        if ($password !== $sure_password) {
            return [501, '确认密码错误'];
        }

        $result = $this->_userM->register($mobile, $password);
        if ($result) {
            return [200, $result];
        }

        return [501, '注册失败'];
    }

    public function imgApi()
    {
//        $this->getComponent('captcha')->getCode();
        return $this->captcha->send();
    }

    public function downloadApi()
    {
        return $this->sendFile(APP_ROOT. '/public/assets/application/images/1457781452.jpg', 'jpg');
    }
}
