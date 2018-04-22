<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 18-4-22
 * Time: 下午6:45
 */
namespace framework\conformancehash;
class DoubleList
{
    protected $_node = null;
    protected $_length = 0;

    public function addNode(Node &$node)
    {
        if (!$node) {
            return false;
        }

        if (!$this->_node) {
            $node->_isFirst = true;
            $this->_node = $node;
        } else {
            $_node = $this->_node;
            while($this->_length > 1) {
                if ($node->_value > $_node->_value && $node->_value < $_node->_next->_value) {
                    break;
                } else {
                    if ($_node->_next->_isFirst) {
                        break;
                    }
                    $_node = $_node->_next;
                }
            }

//            如果找到合适节点的话 这里不会执行   这里是再没有找到并且节点的值小于最后一个几点的时候执行
            if ($_node->_value > $node->_value) {
                $node->_isFirst = true;
                $_node->_next->_isFirst = false;
                $this->_node = $node;
            }

            $node->_prev = $_node;
            if ($_node->_next) {
                $_node->_next->_prev = $node;
            }
            $node->_next = $_node->_next;
            $_node->_next = $node;

        }
        ++$this->_length;
    }

    public function removeNode(Node &$node)
    {
        if (!$node) {
            return false;
        }
        if ($node->_prev) {
            $node->_prev->_next = $node->_next;
        }
        if ($node->_next) {
            $node->_next->_prev = $node->_prev;
        }
        --$this->_length;

        if ($this->_length == 0) {
            $this->_node = null;
        } else if ($node->_isFirst) {
            $this->_node = $node->_next;
            $node->_next->_isFirst = true;
        }
        unset($node);
    }

    public function findNode($value)
    {
        $_node = $this->_node;
        while($_node) {
            if ($_node->_value == $value) {
                return $_node;
            } else {
                if ($_node->_next->_isFirst) {
                    break;
                }
                $_node = $_node->_next;
            }
        }

        return null;
    }
}