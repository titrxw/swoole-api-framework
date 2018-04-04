<?php
namespace framework\components\shutdown;
use framework\base\Component;

class ShutDown extends Component
{
    public function handleShutDown()
    {
        $error = error_get_last();
        $log = '';
        if (!empty($error))
        {
            $message = $error['message'];
            $file = $error['file'];
            $line = $error['line'];
            $log = "$message ($file:$line)\nStack trace:\n";
            $trace = debug_backtrace();
            foreach ($trace as $i => $t) {
                if (!isset($t['file'])) {
                    $t['file'] = 'unknown';
                }
                if (!isset($t['line'])) {
                    $t['line'] = 0;
                }
                if (!isset($t['function'])) {
                    $t['function'] = 'unknown';
                }
                $log .= "#$i {$t['file']}({$t['line']}): ";
                if (isset($t['object']) and is_object($t['object'])) {
                    $log .= get_class($t['object']) . '->';
                }
                $log .= "{$t['function']}()\n";
            }
            $this->getComponent(SYSTEM_APP_NAME, 'log')->save($log);
        }
    }
}