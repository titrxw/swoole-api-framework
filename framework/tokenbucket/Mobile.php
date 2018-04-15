<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 18-4-15
 * Time: 下午4:37
 */
namespace framework\tokenbucket;

class Mobile extends TokenBucket
{
    public function run(\framework\components\request\Request $request, $data = [])
    {
        $this->_key = $data['mobile'] ?? '';
        if (empty($this->_key) || $this->check() === false) {
            $this->triggerException(new \Exception('promise refuse', 500));
        }
    }
}