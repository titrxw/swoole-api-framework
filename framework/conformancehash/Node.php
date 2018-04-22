<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 18-4-22
 * Time: 下午6:44
 */
namespace framework\conformancehash;
class Node
{
    public $_value;
    public $_next;
    public $_prev;
    public $_isFirst = false;

    public function __construct($value)
    {
        $this->_value = $value;
        $this->_next = $this;
        $this->_prev = $this;
    }
}
