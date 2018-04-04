<?php
function autoload($classFile)
{
    $classFile = str_replace('\\','/',$classFile);
    if (file_exists(APP_ROOT.$classFile.'.php'))
        require_once APP_ROOT.$classFile.'.php';
    else 
        throw new \Exception('file ' . APP_ROOT.$classFile.'.php not found', 500);
}

spl_autoload_register('autoload', true, true);