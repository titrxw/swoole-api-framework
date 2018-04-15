<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 18-4-15
 * Time: 下午4:57
 */

namespace framework\tokenbucket;

class Ip extends TokenBucket
{
    public function run(\framework\components\request\Request $request, $data = [])
    {
        // TODO: Implement run() method.
        $this->_key = $request->getClientIp();
        if ($this->check() === false) {
            $this->triggerException(new \Exception('promise refuse', 500));
        }
    }
}