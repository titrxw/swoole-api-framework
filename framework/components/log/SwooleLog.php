<?php
namespace framework\components\log;


class SwooleLog extends Log
{
    public function save($data)
    {
        if ($this->getIsLog()) {
            $time = date('Y-m-d H:i:s');
            $server = $this->getComponent('url')->getServer();
            $dirPath = APP_ROOT . APP_NAME . '/' . $this->getDefaultSavePath() . date('Ym') . '/';
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
            $current_uri = '';
            $remote = '';
            if (!empty($server)) {
                $current_uri = $server['host'] . $server['request_uri'];
                $remote = isset($server['remote_addr']) ? $server['remote_addr'] : '0.0.0.0';
            }

            $info   = '[ log ] ' . $current_uri . "\r\n client: " . $remote  . $depr;

            $this->write("[{$time}] {$info}{$data}\r\n\r\n",$destination);
            unset($server, $data, $destination);
        }
    }
}