<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/9/2
 * Time: 12:11
 */
namespace framework\web;

abstract class Api extends Controller
{
    protected $_responseData = [];


    protected function afterInit()
    {
        
    }

    protected function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->ajax();
        $this->afterInit();
    }

    protected function assign($key, $value = null)
    {
        $this->_responseData[$key] = $value;
    }

    protected function display($path = '')
    {
        return $this->_responseData;
    }
}
