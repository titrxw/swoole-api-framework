<?php
namespace framework\components\log;
use framework\base\Component;

class Log extends Component
{

    /**
     * 日志写入接口
     * @access public
     * @param array $log 日志信息
     * @param bool  $depr 是否写入分割线
     * @return bool
     */
    public function save($data)
    {
        if ($this->getValueFromConf('isLog', true)) {
            $time = date('Y-m-d H:i:s');
            $dirPath = APP_ROOT . $this->getSystem() . '/' . $this->getValueFromConf('path','runtime/log/') . date('Ym') . '/';
            $destination =  date('Ymd') . '.log';
            !is_dir($dirPath) && mkdir($dirPath, 0755, true);

            $i = 1;
            $destination = $dirPath . $destination;
            while (is_file($destination) && floor($this->getValueFromConf('maxSize', 2097152)) <= filesize($destination)) {
                $destination = $dirPath . date('Ymd') . '(' . $i . ')' . '.log';
                ++$i;
            }

            $depr = "\r\n---------------------------------------------------------------\r\n";
            // 获取基本信息
            $current_uri = $_SERVER['HTTP_HOST'] ?? '' . empty($_SERVER['REQUEST_URI']) ?  '' : $_SERVER['REQUEST_URI'];
            $server = $_SERVER['SERVER_ADDR']?? '0.0.0.0';
            $remote = $_SERVER['REMOTE_ADDR']?? '0.0.0.0';

            $info   = '[ log ] ' . $current_uri . "\r\n server: " .  $server . "\r\n client: " . $remote  . $depr;

            $this->write("[{$time}] $info \r\n {$data}\r\n\r\n",$destination);
            unset($destination);
        }
        unset($data);
    }

    public function write($data, $path)
    {
        return error_log($data, 3, $path);
    }
}