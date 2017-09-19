<?php
namespace framework\components\log;


class SwooleLog extends Log
{
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
                rename($destination, dirname($destination) . '/' . $server['request_time'] . '-' . basename($destination));
            }
            $depr = "\r\n---------------------------------------------------------------\r\n";
            // 获取基本信息

            $current_uri = $server['host'] . $server['request_uri'];
            $remote = isset($server['remote_addr']) ? $server['remote_addr'] : '0.0.0.0';

            $info   = '[ log ] ' . $current_uri . "\r\n client: " . $remote  . $depr;

            $this->write("[{$time}] {$info}{$data}\r\n\r\n",$destination);
            unset($server, $data, $destination);
        }
    }
}