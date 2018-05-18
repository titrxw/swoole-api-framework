<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 18-4-22
 * Time: 下午7:58
 */

namespace framework\conformancehash;
class ConformanceNode extends Node
{
    protected $_uniqueId;
    public $_info;
    public $_isVirtual = false;
    public $_pValue;

    public function __construct($data, $rands = '')
    {
        $this->_info = $data;
        $this->init();
        parent::__construct($this->_uniqueId . $rands);
    }

    protected function init()
    {
        $this->_uniqueId = crc32(\serialize($this->_info))  % (2 << 32);
    }


    public function cloneVN()
    {
        $node = new static($this->_info, randStr(9));
        $node->_pValue = $this->_value;
        $node->_isVirtual = true;

        return $node;
    }
}