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

    public function addNode($data)
    {
        $node = new TaskNode($data);
        return $this->_list->addNode($node);
    }

    public function removeNode($data)
    {
        $node = $this->_list->findNode(crc32(\serialize($data)) % (2 << 32));
        return $node && $this->_list->removeNode($node);
    }

    public function findNextNodeByValue($value)
    {
        return $this->_list->findNextNodeByValue($value);
    }

    public function findNode($value)
    {
        $value = crc32(\serialize($value)) % (2 << 32);
        return $this->_list->findNode($value);
    }
}