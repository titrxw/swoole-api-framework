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
    public $_ip;
    public $_host;
    public $_isVirtual = false;
    public $_pValue;

    public function __construct($ip,$host,$rands = '')
    {
        $this->_ip = $ip;
        $this->_host = $host;
        $this->init();
        parent::__construct(crc32($this->_uniqueId . $rands) % (2 << 32));
    }

    protected function init()
    {
        $this->_uniqueId = $this->_ip . $this->_host;
    }


    public function cloneVN()
    {
        $node = new static($this->_ip, $this->_host, randStr(9));
        $node->_pValue = $this->_value;
        $node->_isVirtual = true;

        return $node;
    }
}