<?php
namespace framework\base;

class Model extends Component
{
    protected $_dbHandle;
    protected function init()
    {
        $this->unInstall();
    }

    public function db()
    {
        if (!$this->_dbHandle) {
            $this->_dbHandle = $this->getComponent(SYSTEM_APP_NAME, $this->getValueFromConf('db','meedo'));
        }
        return $this->_dbHandle;
    }
}