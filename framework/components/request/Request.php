<?php
namespace framework\components\request;
use framework\base\Component;

class Request extends Component
{
    protected $_method;
    protected $_rowBody;
    protected $_headers;
    protected $_hasSpecialHtml = false;
    protected $_hasCheck = [];

    protected function init()
    {
        $this->unInstall();
    }

    protected function addStripSlashes(&$data)
    {
        if(is_array($data))
        {
            if(count($data) == 0)
                return $data;
            $keys=array_map('addslashes',array_keys($data));
            $data=array_combine($keys,array_values($data));
            $data = array_map(array($this,'addStripSlashes'),$data);
        }
        else {
            $data = addslashes($data);
            return $data;
        }
    }

    protected function checkData(&$data, $type, $params = '')
    {
        $hasCheck =  $this->_hasCheck[$type][$params] ?? ($this->_hasCheck[$type.'ALL'] ?? false);
        if(empty($this->_hasCheck[$type.'ALL']) && !$hasCheck)
        {
            if (!$this->_hasSpecialHtml){
                \htmlspecialchars($data);
                $this->_hasSpecialHtml = true;
            } 
            if(!function_exists('get_magic_quotes_gpc') || !get_magic_quotes_gpc())
            {
                $data = $params ? $data[$params] : $data;
                $this->addStripSlashes($data);
            }
            if ($params) {
                $this->_hasCheck[$type][$params] = true;
            } else {
                $this->_hasCheck[$type.'ALL'] = true;
            }
        }
    }

    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function get($key = '', $default = '', $needCheck = true)
    {
        return $this->data($_GET, 'get',  $key, $default, $needCheck);
    }

    public function post($key = '', $default = '', $needCheck = true)
    {
        return $this->data($_POST,'post',  $key, $default, $needCheck);
    }

    public function request($key = '', $default = '', $needCheck = true)
    {
        return $this->data($_REQUEST,'request',  $key, $default, $needCheck);
    }

    protected function data(&$data, $type, $key = '', $default = '', $needCheck = true)
    {
        if(!$key)
        {
            $needCheck&&$this->checkData($data, $type, $key);
            return $data;
        }
        else if(!isset($data[$key]))
            return $default;
        else
        {
            $needCheck&&$this->checkData($data, $type, $key);
            return $data[$key];
        }
    }

    public function getRawBody()
    {
        if($this->_rowBody === null)
            $this->_rowBody=file_get_contents('php://input');
        return $this->_rowBody;
    }

    public function isPost()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'post')
        {
            return true;
        }
        return false;
    }


    public function isAjax()
    {
        $result = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest';
        return $result;
    }

    /**
     * 检测是否使用手机访问
     * @access public
     * @return bool
     */
    public function isMobile()
    {
        if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")) {
            return true;
        } elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtoupper($_SERVER['HTTP_ACCEPT']), "VND.WAP.WML")) {
            return true;
        } elseif (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
            return true;
        } elseif (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 当前是否ssl
     * @access public
     * @return bool
     */
    public function isSsl()
    {
        if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
            return true;
        } elseif (isset($_SERVER['REQUEST_SCHEME']) && 'https' == $_SERVER['REQUEST_SCHEME']) {
            return true;
        } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO']) {
            return true;
        }
        return false;
    }


    public function headers()
    {

        if(!empty($this->_headers))
            return $this->_headers;

        foreach ($_SERVER as $name => $value)
        {
            if (substr($name, 0, 5) == 'HTTP_')
            {
                $this->_headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $this->_headers;
    }

    public function header($key)
    {
        $result =  $_SERVER[$key] ?? '';
        return $result;
    }

    public function getClientIp()
    {
        $realip = NULL;

        if (isset($_SERVER))
        {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

                /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
                foreach ($arr AS $ip)
                {
                    $ip = trim($ip);

                    if ($ip != 'unknown')
                    {
                        $realip = $ip;

                        break;
                    }
                }
            }
            elseif (isset($_SERVER['HTTP_CLIENT_IP']))
            {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            }
            else
            {
                if (isset($_SERVER['REMOTE_ADDR']))
                {
                    $realip = $_SERVER['REMOTE_ADDR'];
                }
                else
                {
                    $realip = '0.0.0.0';
                }
            }
        }
        else
        {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            elseif (isset($_SERVER['HTTP_CLIENT_IP']))
            {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            }
            else
            {
                $realip = $_SERVER['REMOTE_ADDR'];
            }
        }

        preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
        $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';

        return $realip;
    }

    public function __destruct()
    {
        unset($_POST,$_GET,$_REQUEST);
    }
}
