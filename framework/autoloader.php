<?php
function autoload($classFile)
{
    $classFile = str_replace('\\','/',$classFile);
    require_file($classFile.'.php');
}

spl_autoload_register('autoload', true, true);