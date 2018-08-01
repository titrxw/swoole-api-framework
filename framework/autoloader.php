<?php
function autoload($classFile)
{
    $classFile = str_replace('\\','/',$classFile);
    require_file($classFile.'.php');
}

// 保证该加载器在第一个
spl_autoload_register('autoload', true, true);