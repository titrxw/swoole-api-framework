<?php
namespace framework\components\request;
use framework\base\Component;

class Request extends Component
{
    protected $_method;
    protected $_headers;
    protected $_hasCheck = [];
    protected $_get = null;
    protected $_post = null;
    protected $_put = null;
    protected $_input = null;

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
            return $data;
        }
        else {
            if (gettype($data) !== 'string') {
                return $data;
            }
            $data = \htmlspecialchars($data);
            $data = addslashes($data);
            return $data;
        }
    }

    protected function checkData(&$data, $type, $params = '')
    {
        $hasCheck =  $this->_hasCheck[$type][$params] ?? ($this->_hasCheck[$type.'ALL'] ?? false);
        if(empty($this->_hasCheck[$type.'ALL']) && !$hasCheck)
        {
            if(!function_exists('get_magic_quotes_gpc') || !get_magic_quotes_gpc())
            {
                if ($params) {
                    $this->addStripSlashes($data[$params]);
                } else {
                    $this->addStripSlashes($data);
                }
            }
            if ($params) {
                $this->_hasCheck[$type][$params] = true;
            } else {
                $this->_hasCheck[$type.'ALL'] = true;
            }
        }
    }

    public function method()
    {  
        if ($this->_method) {
            return $this->_method;
        }

        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $this->_method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
        } else {
            $this->_method = IS_CLI ? 'GET' : (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : $_SERVER['REQUEST_METHOD']);
        }
        return $this->_method;
    }

    public function get($key = '', $default = null, $filter = true)
    {
        if (is_null($this->_get)) {
            $this->_get = $_GET;
        }
        return $this->data($this->_get, 'get',  $key, $default, $filter);
    }

    public function post($key = '', $default = null, $filter = true)
    {
        if (is_null($this->_post)) {
            if (empty($_POST) && 'application/json' == $this->contentType()) {
                $content = $this->input();
                $this->_post = (array) json_decode($content, true);
            } else {
                $this->_post = $_POST;
            }
        }
        return $this->data($this->_post,'post',  $key, $default, $filter);
    }

    public function request($key = '', $default = null, $filter = true)
    {
        return $this->data($_REQUEST,'request',  $key, $default, $filter);
    }

    public function put($key = '', $default = null, $filter = true)
    {
        if (is_null($this->_put)) {
            $content = $this->input();
            if ('application/json' == $this->contentType()) {
                $this->_put = (array) json_decode($content, true);
            } else {
                parse_str($content, $this->_put);
            }
        }

        return $this->data($this->_put,'put',  $key, $default, $filter);
    }

    public function delete($key = '', $default = null, $filter = true)
    {
        return $this->put($key, $default, $filter);
    }

    public function patch($key = '', $default = null, $filter = true)
    {
        return $this->put($key, $default, $filter);
    }

    protected function data(&$data, $type, $key = '', $default = '', $filter = true)
    {
        if(!$key)
        {
            $filter&&$this->checkData($data, $type, $key);
            return $data;
        }
        else if(!isset($data[$key]))
            return $default;
        else
        {
            $filter&&$this->checkData($data, $type, $key);
            return $data[$key];
        }
    }

    public function input()
    {
        if($this->_input === null)
            $this->_input=file_get_contents('php://input');
        return $this->_input;
    }

    
    /**
     * 当前请求 HTTP_CONTENT_TYPE
     * @access public
     * @return string
     */
    public function contentType()
    {
        if (isset($_SERVER['CONTENT_TYPE'])) {
            list($type) = explode(';', $_SERVER['CONTENT_TYPE']);
            return trim($type);
        }
        return '';
    }

    public function isGet()
    {
        return $this->method() == 'GET';
    }

    public function isPost()
    {
        if ($this->method() === 'POST')
        {
            return true;
        }
        return false;
    }

    public function isPut()
    {
        return $this->method() == 'PUT';
    }

    public function isDelete()
    {
        return $this->method() == 'DELETE';
    }

    public function isHead()
    {
        return $this->method() == 'HEAD';
    }

    public function isPatch()
    {
        return $this->method() == 'PATCH';
    }

    public function isOptions()
    {
        return $this->method() == 'OPTIONS';
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
