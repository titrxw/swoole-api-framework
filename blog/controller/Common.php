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

    protected function rule()
    {
        return array(
            'loginApi' => array(
               'mobile|post|账号格式错误'=>'regex|/^1[34578]\d{9}$/',
               'password|post|密码格式错误' => 'require'
            ),
            'registerApi' => array(
                'mobile|post|账号格式错误'=>'regex|/^1[34578]\d{9}$/',
                'password|post|密码格式错误' => 'require',
                'sure_password|post|确认密码格式错误' => 'require'
            )
        );
    }

    protected function afterInit()
    {
        $this->_userM = $this->model('User');
    }

    public function testApi()
    {
        $client = new \swoole_client(SWOOLE_SOCK_TCP);
        if (!$client->connect('127.0.0.1', 8080, -1))
        {
            echo ("connect failed. Error: {$client->errCode}\n");
        }
        $client->send('publish');
        $result = $client->recv();
        if ($result) {
            $client->send( \json_encode([
                '/1 * * 4-6 *--crontabTask test',
                '/1 * * 5-6 *--sendMsg sendMsg'
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
        $mobile = $this->request->post('mobile');
        $password = $this->request->post('password');

        $result = $this->_userM->login($mobile, $password);
        if ($result) {
            return [200, $result];
        }

        return [501, '登录失败'];
    }

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

    public function imgAction()
    {
//        $this->getComponent('captcha')->getCode();
        return $this->captcha->send();
    }

    public function downloadAction()
    {
        return $this->sendFile(APP_ROOT. '/public/assets/' . APP_NAME. '/images/1457781452.jpg', 'jpg');
    }
}