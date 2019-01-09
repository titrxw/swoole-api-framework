<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/10/25
 * Time: 21:59
 */
namespace framework\components\security;
use framework\base\Component;

class Rsa extends Component
{
    /**
     * 私钥
     * @var unknown
     */
    protected $_privatekey = null;

    /**
     * 公钥
     * @var unknown
     */
    protected $_publickey = null;


    protected function init()
    {
        $this->initPrvKey();
        $this->initPublicKey();
    }

    /**
     * 获取秘钥
     * @param unknown $path
     */
    protected function getKey($path)
    {
        if (\strlen($path) > 200) {
            return $path;
        }
        $path = 'file://'.APP_ROOT.$path;
        if(file_exists($path))
            return file_get_contents($path);
        else
            throw new \Exception('RAS init security key file ' . $path . ' not exists ', 500);
    }

    /**
     * 初始化私钥
     */
    protected function initPrvKey()
    {
        /**
         *
         * 从文件获取
         */
        $keyPath = $this->getValueFromConf('private_key', '');
        if(!empty($keyPath))
        {
            $this->_privatekey=openssl_get_privatekey($this->getKey($keyPath));
        }
        else
            throw new \Exception('RAS private key file not exists ', 500);
    }

    /**
     * 初始化公钥
     */
    protected function initPublicKey()
    {
        $keyPath = $this->getValueFromConf('public_key', '');
        if(!empty($keyPath))
        {
            $this->_publickey=openssl_get_publickey($this->getKey($keyPath));
        }
        else
            throw new \Exception('RAS public key file not exists ', 500);
    }


    /**
     * 进行公钥加密
     * @param unknown $data
     * @param string $padding
     */
    public function encrypt($data,$padding=OPENSSL_PKCS1_PADDING)
    {
        $result=null;
        if(!empty($data))
        {
            openssl_public_encrypt($data, $result, $this->_publickey, $padding);
        }
        if(!empty($result))
            return base64_encode($result);
    }

    /**
     * 私钥揭秘数据
     * @param unknown $data
     * @param string $padding
     * @return string
     */
    public function decrypt($data,$padding=OPENSSL_PKCS1_PADDING)
    {
        $result=null;
        if(!empty($data))
        {
            $data=base64_decode($data);
            openssl_private_decrypt($data, $result, $this->_privatekey, $padding);
        }
        if(!empty($result))
            return ($result);
        return null;
    }

    /**
     * 私钥签名数据
     * @param unknown $data
     * @return string|NULL
     */
    public function sign($data,$sign_alt=OPENSSL_ALGO_SHA256)
    {
        if(!empty($data))
        {
            $ret = false;
            if (openssl_sign($data, $ret, $this->_privatekey,$sign_alt))
                $ret = base64_encode($ret);
            return $ret;
        }
        return null;
    }

    /**
     * 公钥验证数据
     * @param unknown $data
     * @param unknown $sign
     * @return boolean
     */
    public function verify($data,$sign,$sign_alt=OPENSSL_ALGO_SHA256)
    {
        $ret = false;
        $sign = base64_decode($sign);
        if ($sign !== false)
        {
            switch (openssl_verify($data, $sign, $this->_publickey,$sign_alt))
            {
                case 1:
                    $ret = true;
                    break;
                default:
                    $ret = false;
                    break;
            }
        }
        return $ret;
    }
    /**
     * 释放对应的秘钥
     */
    public function __destruct()
    {
        if (!empty($this->_privatekey))
            openssl_free_key($this->_privatekey);
        if (!empty($this->_publickey))
            openssl_free_key($this->_publickey);
    }
}