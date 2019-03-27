<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 18-4-21
 * Time: 下午9:16
 */


 /**
  * 全局变量
  */
  $ALL_MODULES = [];



class MacAddr{

    protected $return_array = array(); // 返回带有MAC地址的字串数组
    protected $mac_addr;

    function GetMacAddr($os_type){
        if ($this->mac_addr) {
            return $this->mac_addr;
        }
        switch ( strtolower($os_type) ){
            case "linux":
                $this->forLinux();
                break;
            case "solaris":
                break;
            case "unix":
                break;
            case "aix":
                break;
            default:
                $this->forWindows();
                break;

        }


        $temp_array = array();
        foreach ( $this->return_array as $value ){

            if (
            preg_match("/[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f][:-]"."[0-9a-f][0-9a-f]/i",$value,
                $temp_array ) ){
                $this->mac_addr = $temp_array[0];
                break;
            }

        }
        unset($temp_array);
        return $this->mac_addr;
    }


    function forWindows(){
        @exec("ipconfig /all", $this->return_array);
        if ( $this->return_array )
            return $this->return_array;
        else{
            $ipconfig = $_SERVER["WINDIR"]."\system32\ipconfig.exe";
            if ( is_file($ipconfig) )
                @exec($ipconfig." /all", $this->return_array);
            else
                @exec($_SERVER["WINDIR"]."\system\ipconfig.exe /all", $this->return_array);
            return $this->return_array;
        }
    }



    function forLinux(){
        @exec("ifconfig -a", $this->return_array);
        return $this->return_array;
    }

}



if (!\function_exists('getMacAddr')) {
    function GetMacAddr($os_type)
    {
        $mac = new MacAddr();
        return  $mac->GetMacAddr($os_type);
    }
}

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

if (!function_exists('hash32')) {
    function hash32 ($str)
    {
        return crc32($str) >> 16 & 0x7FFFFFFF; 
    }
}

if (!function_exists('hash33')) {
    function hash33 ($str)
    {
        $hash = 5381;
        $s    = md5($str); //相比其它版本，进行了md5加密
        $seed = 5;
        $len  = 32;//加密后长度32
        for ($i = 0; $i < $len; $i++) {
            // (hash << 5) + hash 相当于 hash * 33
            //$hash = sprintf("%u", $hash * 33) + ord($s{$i});
            //$hash = ($hash * 33 + ord($s{$i})) & 0x7FFFFFFF;
            $hash = ($hash << $seed) + $hash + ord($s{$i});
        }
 
        return $hash & 0x7FFFFFFF;  
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
        $token = md5($prefx.$data.SYSTEM_CD_KEY.SYSTEM_WORK_ID.$salt);
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
        $instance = \framework\base\Container::getInstance()->getComponent(\getModule(), 'uniqueid');
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
if (!function_exists('coroutine')) {
    function coroutine ($callback, $params = []) {
        if (!function_exists('go')) {
            throw new Error('function go not support ', 500);
        }
        $id = \Swoole\Coroutine::getuid();

        go (function () use ($id, $callback, $params) {
            if ($callback instanceof \Closure ) {
                $callback($params);
            } else if (is_array($callback)) {
                call_user_func_array($callback, $params);
            } else {
                throw new Error('arg error ', 500);
            }
            
            \Swoole\Coroutine::resume($id);
        });
        if (swoole_version() < 4) {
            \Swoole\Coroutine::suspend($id);
        } else {
            \Swoole\Coroutine::suspend();
        }
        
    }
}
