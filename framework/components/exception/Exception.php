<?php
namespace framework\components\exception;
use framework\base\Component;

class Exception extends Component
{
    public  function handleException($exception)
    {
        $msg = $this->formartMessage($exception);
        $GLOBALS['EXCEPTION'] = $msg;
        $this->getComponent(SYSTEM_APP_NAME, 'log')->save($msg);
    }

    public function formartMessage($exception)
    {
        return " msg : " . $exception->getMessage() .
            " \r\n file : " . $exception->getFile().
            " \r\n line : " . $exception->getLine().
            " \r\n code : " . $exception->getCode().
            " \r\n trace : " . $exception->getTraceAsString();
    }
}
