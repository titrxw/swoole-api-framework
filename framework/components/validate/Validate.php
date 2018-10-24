<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/9/6
 * Time: 22:35
 */
namespace framework\components\validate;

use framework\base\Component;

class Validate extends Component
{
    protected $_separator;
    protected $_keySeparator;
    protected $_data;
    protected $_defaultMsg = '参数错误';

    protected function init()
    {
        $this->_separator = $this->getValueFromConf('separator', '|');
        $this->_keySeparator = $this->getValueFromConf('key_separator', '.');
    }

    public function run($data, $rule)
    {
        $this->_data = $data;
        foreach ($rule as $key=>$item)
        {
            $key = explode($this->_separator, $key);
            $_data = $this->getDataByKey($key);
            unset($key);
            if (!$_data)
            {
                unset($data, $rule);
                $this->finish();
                return false;
            }
            else
            {
                $result = $this->validateValue($_data['value'], $item);
                if ($result !== true)
                {
                    unset($data, $rule);
                    $this->finish();
                    return $_data['msg'];
                }
            }
        }
        unset($data, $rule);
        $this->finish();
        return true;
    }

    protected function getDataByKey($key)
    {
        if (empty($key[0]))
        {
            return null;
        }
        $data = '';
        $msg = '';
        $valKey = explode($this->_keySeparator, $key[0]);
        if(!empty($key[1]) && ($key[1] === 'post' || $key[1] === 'get'))
        {

            $data = $this->_data[$key[1]];
            foreach($valKey as $item) {
                if (!$item) {
                    continue;
                }
                $data = $data[$item] ?? null;
            }
            $msg = empty($key[2]) ? $this->_defaultMsg : $key[2];
        }
        else
        {
            $data = array_merge($this->_data['get'], $this->_data['post']);
            foreach($valKey as $item) {
                if (!$item) {
                    continue;
                }
                $data = $data[$item] ?? null;
            }
            
            $msg = empty($key[1]) ? $this->_defaultMsg : $key[1];
        }

        unset($key);
        return array(
            'value' => $data,
            'msg' => $msg
        );
    }

    protected function validateValue($value, $rule)
    {
        if (!$rule)
        {
            unset($value);
            return true;
        }
        $rule = explode($this->_separator, $rule);
        $result = true;
        foreach ($rule as $key=>$item)
        {
            if (!$result)
            {
                break;
            }
            switch ($item)
            {
                case 'require':
                    $result = $this->checkEmpty($value);
                    break;
                case "integer":
                    $result = $this->checkInteger($value);
                    break;
                case "regex":
                    $result = $this->checkRegex($value,$rule[$key+1]);
                    break;
            }
        }
        unset($rule);
        return $result;
    }

    protected function checkEmpty($value)
    {
        if ($value)
        {
            return true;
        }
        return false;
    }

    protected function checkInteger($value)
    {
        if ($value === 0 || $value === '0' || preg_match('/^[^0]\d*$/',$value))
        {
            return true;
        }
        return false;
    }

    protected function checkRegex($value,$rule)
    {
        if(!$rule)
        {
            return true;
        }
        if (preg_match($rule, $value))
        {
            return true;
        }
        return false;
    }

    protected function finish()
    {
        $this->_data = [];
    }
}
