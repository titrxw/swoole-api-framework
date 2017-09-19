<?php
function autoload($classFile)
{
    $classFile = str_replace('\\','/',$classFile);
    if (file_exists(APP_ROOT . '/'.$classFile.'.php'))
        require_once APP_ROOT . '/'.$classFile.'.php';
}

spl_autoload_register('autoload', true, true);