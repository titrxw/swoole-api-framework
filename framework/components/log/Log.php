<?php
namespace framework\components\log;
use framework\base\Component;

class Log extends Component
{
    protected $_savePath;
    protected $_isLog = null;
    protected $_maxSize;

    /**
     * 日志写入接口
     * @access public
     * @param array $log 日志信息
     * @param bool  $depr 是否写入分割线
     * @return bool
     */
    public function save($data)
    {
        if ($this->getIsLog()) {
            $time = date('Y-m-d H:i:s');
            $server = $this->getComponent('url')->getServer();
            $dirPath = APP_ROOT . '/' . APP_NAME . '/' . $this->getDefaultSavePath() . date('Ym') . '/';
            $destination =  date('Ymd') . '.log';
            !is_dir($dirPath) && mkdir($dirPath, 0755, true);

            $i = 1;
            $destination = $dirPath . $destination;
            while (is_file($destination) && floor($this->getMaxSize()) <= filesize($destination)) {
                $destination = $dirPath . date('Ymd') . '(' . $i . ')' . '.log';
                ++$i;
            }

            $depr = "\r\n---------------------------------------------------------------\r\n";
            // 获取基本信息
            $current_uri = $server['HTTP_HOST'] . $server['REQUEST_URI'];
            $server = isset($server['SERVER_ADDR']) ? $server['SERVER_ADDR'] : '0.0.0.0';
            $remote = isset($server['REMOTE_ADDR']) ? $server['REMOTE_ADDR'] : '0.0.0.0';

            $info   = '[ log ] ' . $current_uri . "\r\n server: " .  $server . "\r\n client: " . $remote  . $depr;

            $this->write("[{$time}] {$info}{$data}\r\n\r\n",$destination);
            unset($server, $data, $destination);
        }
    }

    public function write($data, $path)
    {
        return error_log($data, 3, $path);
    }

    protected function getDefaultSavePath()
    {
        if(empty($this->_savePath))
        {
            $this->_savePath = $this->getValueFromConf('path','runtime/log/');
        }
        return $this->_savePath;
    }

    protected function getIsLog()
    {
        if (!isset($this->_isLog))
        {
            $this->_isLog = $this->getValueFromConf('debug', true);
        }
        return $this->_isLog;
    }

    protected function getMaxSize()
    {
        if (!isset($this->_maxSize))
        {
            $this->_maxSize = $this->getValueFromConf('maxSize', 2097152);
        }
        return $this->_maxSize;
    }
}