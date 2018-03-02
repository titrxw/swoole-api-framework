<?php
namespace framework\crontab;


class TaskTime
{
    protected $_start = -1;
    protected $_step = -1;
    protected $_unit = 1;
    protected $_end = -1;
    protected $_val = [];
    protected $_rule = -1;

    public function __construct($conf)
    {
        if (empty($conf)) {
            throw new \Exception('rule error');
        }
        $this->_rule = $conf;
        if ($conf == '*') {
            return true;
        }

        $conf = explode('/', $conf);
        if (!empty($conf[1])) {
            if ($conf[1] <= 0) {
                throw new \Exception('rule error');
            }
            $this->_step = (int) $conf[1];
        }
        $conf = explode('-', $conf[0]);
        if (!empty($conf[1])) {
            $conf[0] = (int) $conf[0];
            $conf[1] = (int) $conf[1];
            if ($conf[0] <= 0 || $conf[1]<= 0) {
                throw new \Exception('rule error');
            }
            if ($conf[1] <= $conf[0]) {
                throw new \Exception('rule error');
            }
            $this->_start = $conf[0];
            $this->_end = $conf[1];
            if ($this->_step < 0) {
                $this->_step = $this->_unit;
            }
            $this->_val = array('step');
        } else {
            if (empty($conf[0]) && $this->_step > 0) {
                $this->_start = 0;
                $this->_end = 'auto';
                $this->_val = array('auto');
            } else {
                if (empty($conf[0])) {
                    throw new \Exception('rule error');
                }
                $conf = explode(',', $conf[0]);
                foreach($conf as $item) {
                    $item = (int) $item;
                    if ($item <= 0) {
                        throw new \Exception('rule error');
                    }
                    $this->_val[] = $item;
                }
                $this->_step = -1;
            }
        }
        unset($conf);
    }

    private function getEnd($val)
    {
        if ($this->_end === 'auto') {
            $this->_end = $val + $this->_step;
        }
    }

    public function check($val)
    {
        $val = (int) $val;
        if (empty($this->_val)) {
            return true;
        }
        $this->getEnd($val);
        if ($this->_start >= 0 && $this->_end >= 0) {
            if ($val<= $this->_end && is_integer(($val - $this->_start) / $this->_step)) {
                return true;
            }
            return false;
        }
        foreach ($this->_val as $item) {
            if ($item == $val) {
                return true;
            }
        }

        return false;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }
}