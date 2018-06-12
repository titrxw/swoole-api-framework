<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 18-4-21
 * Time: 下午9:16
 */
if (!function_exists('require_file')) {
    function require_file($path)
    {
        if (!$path || !file_exists(APP_ROOT . $path)) {
            throw new Error('file '. APP_ROOT . $path .' not exists', 404);
        }
        return require APP_ROOT . $path;
    }
}

if (!function_exists('require_file_once')) {
    function require_file_once($path)
    {
        if (!$path || !file_exists(APP_ROOT . $path)) {
            throw new Error('file '. APP_ROOT . $path .' not exists', 404);
        }
        return require_once APP_ROOT . $path;
    }
}

if (!function_exists('include_file')) {
    function include_file($path)
    {
        if (!$path || !file_exists(APP_ROOT . $path)) {
            throw new Error('file '. APP_ROOT . $path .' not exists', 404);
        }
        return include APP_ROOT . $path;
    }
}

if (!function_exists('include_file_once')) {
    function include_file_once($path)
    {
        if (!$path || !file_exists(APP_ROOT . $path)) {
            throw new Error('file '. APP_ROOT . $path .' not exists', 404);
        }
        return include_once APP_ROOT . $path;
    }
}

if (!function_exists('randStr')) {
    function randStr($len = 8)
    {
        $codes = "0123456789abcdefghijkmnpqrstuvwxyABCDEFGHIJKLMNPQRSTUVWXY";

        $randStr = "";

        for($i=0; $i < $len; $i++)
        {
            $randStr .=$codes{mt_rand(0, strlen($codes)-1)};
        }

        return $randStr;
    }
}

if (!function_exists('randNumber')) {
    function randNumber($len = 8)
    {
        $codes = "01234567890123456789012345678901234567890123456789";

        $randNumber = "";
        $_len = strlen($codes) - 1;

        for($i=0; $i < $len; $i++)
        {
            $randNumber .=$codes{mt_rand(0, $_len)};
        }

        return $randNumber;
    }
}

if (!function_exists('token')) {
    function token($data, $prefx='easy')
    {
        $data = is_array($data) ? json_encode($data) : $data;
        $salt = microtime();
        $token = md5($prefx.$data.SYSTEM_WORK_ID.$salt);
        return $token;
    }
}

if (!function_exists('getModule')) {
    function getModule()
    {
        return $_SERVER['CURRENT_SYSTEM'] ?? '';
    }
}

if (!function_exists('uniqueId')) {
    function uniqueId()
    {
        $instance = \framework\base\Container::getInstance()->getComponent(getModule(), 'uniqueid');
        return $instance->nextId();
    }
}

if (!function_exists('securityIdCard')) {
    function securityIdCard($idCard)
    {
        return substr_replace($idCard, '***********', 3, 11);
    }
}

if (!function_exists('securityMobile')) {
    function securityMobile($mobile)
    {
        return substr_replace($mobile, '****', 3, 4);
    }
}
if (!\function_exists('getFiles')) {
    function getFiles($dir, $type= '*', $ext = '*')
    {
        $files = array();
        if ( $handle = \opendir($dir) ) {
            while ( ($file = \readdir($handle)) !== false ) {
                //过滤隐藏文件
                $ArrFileName = \explode('.', $file);
                if ( $file != ".." && $file != "." && $ArrFileName[0]) {
                    $_file=array();
                    if ($type === '*' || $type === 'dir') {
                        if (\is_dir($dir . "/" . $file) ) {
                            $_file['name'] = $dir . "/" . $file;
                            $_file['time'] = @\filemtime($dir . "/" . $file);
                            $_file['type'] = 'dir';
                            $type === 'dir' && $files = \array_merge($files, \getFiles($dir . "/" . $file, $type, $ext));
                        }
                    }
                    if ($type === '*' || $type === 'file') {
                        if (\is_dir($dir . "/" . $file) ) {
                            $files = \array_merge($files, \getFiles($dir . "/" . $file, $type, $ext));
                        } else {
                            $fext=\strrchr($file,'.');
                            if ($ext !== '*' && $fext !== $ext) {
                                continue;
                            }
                            $_file['name'] = $dir . "/" . $file;
                            $_file['time'] = @\filemtime($dir . "/" .$file);
                            $_file['type'] = 'file';
                        }
                    }

                    if(!empty($_file)){
                        $files[]=$_file;
                        unset($_file);
                    }
                }
            }
            \closedir($handle);
        }
        return $files;
    }
}