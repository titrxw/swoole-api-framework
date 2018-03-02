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
        var_dump($this->request->get());
//        php gc 可以理解为只处理循环引用，

//        $this->redis->set('er', 123);
//        print_r($this->_userM->getList());
//        $this->getRedis()->set('test', 12);
//        if (function_exists('hello_word')) {
//            var_dump(hello_word($this->getAction()));
//            return [404, ['er','ererer']];
//        }
//        定时器
//        $this->addTimer(10000, function ($id, $params) {
//            var_dump($params);
////            swoole_timer_clear($id);
//        }, ['er','ererer']);
//        再10000毫秒后执行  执行完后自动清理计时器   该函数返回计时器id
//         $this->addTimerAfter(10000, function ($params) {
//             var_dump($params);
//         }, ['er','ererer']);
// //        for($i=0; $i<10;$i++)
// //        {
// //            $this->addTask('msgTask', 'sendMsg', array('mobile' => '1212121212'));
// //        }
//         $this->addTask('msgTask', 'sendMsg', array('mobile' => '1212121212'));
//        var_dump($this->cache);
//        var_dump($this->session);
        //var_dump($this->getComponent('Logger',1));
        return [404, ['er','�߸���߸���ָ����ߺ͹���'],'�߸���߸���ָ����ߺ͹���'];
    }

    public function testAction()
    {
//        var_dump($this->getComponent('db'));
//        var_dump($this->getComponent('log'));
        $result = $this->_userM->getList();
        return [200, $result,'fds'];
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