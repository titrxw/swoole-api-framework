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
    public function __construct()
    {
        $this->_list = new ConformanceHash();
    }

    public function addNode($ip,$host)
    {
        $node = new ConformanceNode($ip, $host);
        $this->_list->addNode($node);
    }

    public function removeNode($ip,$host)
    {
        $node = $this->_list->findNode(crc32($ip . $host) % (2 << 8));
        $this->_list->removeNode($node);
        unset($node);
    }

    public function findNodeByValue($value)
    {
        $node = $this->_list->findNodeByValue($value);
        var_dump($node);
    }
}