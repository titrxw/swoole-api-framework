<?php
namespace framework\components\error;
use framework\base\Component;

class Error extends Component
{
    public  function handleError($code, $message, $file, $line)
    {
        throw new \Exception($message . ' at  file: ' .$file.", on line: " . $line , $code);
    }
}