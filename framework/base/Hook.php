<?php

namespace framework\base;

class Hook extends Component
{
    private $_tags = [];

    public function add($tag, $behavior, $first = false)
    {
        isset($this->_tags[$tag]) || $this->_tags[$tag] = [];

        if (is_array($behavior)) {
            $this->_tags[$tag] = array_merge($this->_tags[$tag], $behavior);
        } elseif ($first) {
            array_unshift($this->_tags[$tag], $behavior);
        } else {
            $this->_tags[$tag][] = $behavior;
        }
    }

    public function get($tag = '')
    {
        if (empty($tag)) {
            //获取全部的插件信息
            return $this->_tags;
        } else {
            return array_key_exists($tag, $this->_tags) ? $this->_tags[$tag] : [];
        }
    }

    public function triggerAll($tag, &$params = null, $extra = null, $once = false)
    {
        $results = [];

        $tags    = $this->get($tag);
        foreach ($tags as $key => $name) {
            $results[$key] = $this->trigger($name, $tag, $params, $extra);
            if (false === $results[$key]) {
                // 如果返回false 则中断行为执行
                break;
            } elseif (!is_null($results[$key]) && $once) {
                break;
            }
        }
        return $once ? end($results) : $results;
    }

    public function trigger($class, $tag = '', &$params = null, $extra = null)
    {
        if ($class instanceof \Closure) {
            $result = call_user_func_array($class, [ & $params, $extra]);
            $class  = 'Closure';
        } elseif (is_array($class)) {
            list($class, $method) = $class;

            $result = (new $class())->$method($params, $extra);
            $class  = $class . '->' . $method;
        } elseif (is_object($class)) {
            $result = $class->$method($params, $extra);
            $class  = get_class($class);
        } elseif (strpos($class, '::')) {
            $result = call_user_func_array($class, [ & $params, $extra]);
        } else {
            $obj    = new $class();
            $method = ($tag && is_callable([$obj, $method])) ? $method : 'attach';
            $result = $obj->$method($params, $extra);
        }
        return $result;
    }

}
