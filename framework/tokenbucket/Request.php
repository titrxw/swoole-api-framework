<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 18-3-26
 * Time: 下午9:20
 */
namespace framework\tokenbucket;

class Request extends TokenBucket
{
    public function run(\framework\components\request\Request $request, $data = [])
    {
        // TODO: Implement check() method.
        if ($this->check() === false) {
            $this->triggerException(new \Exception('promise refuse', 500));
        }
    }
}