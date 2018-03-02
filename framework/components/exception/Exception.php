<?php
namespace framework\components\exception;
use framework\base\Component;

class Exception extends Component
{
    public  function handleException($exception)
    {
        $this->getComponent(SYSTEM_APP_NAME, 'log')->save($this->formartMessage($exception));
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