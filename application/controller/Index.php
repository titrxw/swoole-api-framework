<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/8/27
 * Time: 20:58
 */
namespace application\controller;
use application\lib\Web;

class Index extends Web
{
    private $_userM;

    protected function rule()
    {
        return array(
//            'testAction' => array(
//                'id|get|请求编号'=>'require|integer',
//                'mobile|get|电话号码' => 'regex|/^1[34578]\d{9}$/'
//            ),
//            'testAction' => array(
//                'id|post|请求编号'=>'url',
//                'name|post|请求姓名' => 'require',
//            )
        );
    }

    protected function init()
    {
        $this->_userM = $this->model('User');
        parent::init(); // 这里必须有  在运行结束后要回收
    }

    public function indexAction()
    {
        $this->addTask('msgTask', 'sendMsg', array('mobile' => '1212121212'));
//        var_dump($this->cache);
//        var_dump($this->session);
        //var_dump($this->getComponent('Logger',1));
        //return $this->sendFile(APP_ROOT. '/public/assets/' . APP_NAME. '/images/1457781452.jpg', 'zip');
        return [404, ['er','ererer'],'fds'];
    }

    public function testAction()
    {
//        var_dump($this->getComponent('Logger', 1)); composer 获取方式
        $result = $this->_userM->getList();
        return [404, $result,'fds'];
    }
}