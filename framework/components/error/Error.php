<?php
namespace framework\components\error;
use framework\base\Component;

class Error extends Component
{
    public  function handleError($code, $message, $file, $line, $else)
    {
        $msg = $code . ' ' . $message . ' at  file: ' .$file.", on line: " . $line . "\n stace:\n" . json_encode($else);
        DEBUG && $GLOBALS['ERROR'] = $msg;
        $this->getComponent(SYSTEM_APP_NAME, 'log')->save($msg);
    }
}
