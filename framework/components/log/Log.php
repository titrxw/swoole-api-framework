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
            $destination = APP_ROOT . '/' . APP_NAME . '/' . $this->getDefaultSavePath() . date('Ymd') . '.log';
            $path = dirname($destination);
            !is_dir($path) && mkdir($path, 0755, true);

            //检测日志文件大小，超过配置大小则备份日志文件重新生成
            if (is_file($destination) && floor($this->getMaxSize()) <= filesize($destination)) {
                rename($destination, dirname($destination) . '/' . $server['REQUEST_TIME'] ?? '' . '-' . basename($destination));
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