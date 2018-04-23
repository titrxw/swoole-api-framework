<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 18-4-22
 * Time: 下午6:44
 */

namespace framework\conformancehash;
class Dispatcher
{
    protected $_list;
    protected $_maxNodeNums = 5;

    public function __construct()
    {
        $this->_list = new ConformanceHash();
    }

    public function addVirtualNode()
    {
        $this->_list->addVirtualNode($this->_maxNodeNums);
    }

    public function addNode($ip,$host)
    {
        $node = new ConformanceNode($ip, $host);
        $this->_list->addNode($node);
    }

    public function removeNode($ip,$host)
    {
        $node = $this->_list->findNode(crc32($ip . $host) % (2 << 32));
        $this->_list->removeNode($node);
        unset($node);
    }

    public function findNextNodeByValue($value)
    {
        $node = $this->_list->findNextNodeByValue($value);
        var_dump($node);
    }
}