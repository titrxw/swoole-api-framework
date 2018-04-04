<?php
namespace framework\components\security;
use framework\base\Component;

class Password extends Component
{
    private $_value;
    /**
     * 数据处理方式
     * @var unknown
     */
    private $PBKDF2_HASH_ALGORITHM="sha256";
    /**
     * 机密数据循环次数
     * @var unknown
     */
    private $PBKDF2_ITERATIONS=3000;
    /**
     * 
     * 加密用盐的长度
     * @var unknown
     */
    private $PBKDF2_SALT_BYTE_SIZE=16;
    /**
     * hash数据的长度
     * @var unknown
     */
    private $PBKDF2_HASH_BYTE_SIZE=32;
    
    /**
     * 保存hash加盐
     * @var unknown
     */
    private $HashSalt;
    /**
     * 加密结果
     * @var unknown
     */
    private $HashStr;
    
    protected function init()
    {
        $this->unInstall(false);
    }
    
    public function setPassword($val)
    {
        $this->_value = $val;
        return $this;
    }

    public function setHash($val)
    {
        $this->HashStr = $val;
        return $this;
    }

    public function setSalt($val)
    {
        $this->HashSalt = $val;
        return $this;
    }


    /**
     * 申城hash眼
     */
    private function MakeHashSalt()
    {
        if(function_exists("openssl_random_pseudo_bytes"))
            $salt = base64_encode(openssl_random_pseudo_bytes($this->PBKDF2_SALT_BYTE_SIZE ));
        else
            $salt=base64_encode($this->MakeTmpSalt());
        $this->HashSalt=$salt;
    }
    
    /**
     * 生成临时的salt
     */
    private function MakeTmpSalt()
    {
        return $this->getComponent(SYSTEM_APP_NAME, 'helper')->randStr($this->PBKDF2_SALT_BYTE_SIZE);
    }
    
    /**
     * 获取盐
     * @return unknown
     */
    public function GetHashSalt()
    {
        return $this->HashSalt;
    }
    
    
    public function GetHashStr()
    {
        return  $this->HashStr;
    }
    /**
     * 生成密码
     */
    public function MakeHashStr()
    {
        /**
         * 生成盐
         */
        $this->MakeHashSalt();
        
        /**
         * 组合密码
         */
        $this->HashStr= base64_encode($this->pbkdf2(
                $this->PBKDF2_HASH_ALGORITHM,
                $this->_value,
                $this->HashSalt,
                $this->PBKDF2_ITERATIONS,
                $this->PBKDF2_HASH_BYTE_SIZE,
                true
            ));

        return $this->HashStr;
    }
    
    /**
     * 检测密码是否正确
     * @param unknown $hansstr
     */
    public function validate()
    {
        /**
         * 确保三要素不为空
         */
        if(!$this->HashStr||!$this->HashSalt||!$this->_value)
            return false;
        $pbkdf2 = base64_decode($this->HashStr);
        return $this->SlowEquals(
            $pbkdf2,
            $this->pbkdf2(
                $this->PBKDF2_HASH_ALGORITHM,
                $this->_value,
                $this->HashSalt,
                $this->PBKDF2_ITERATIONS,
                strlen($pbkdf2),
                true
            )
        );
    }
    
    /**
     * 检测是不是相同
     * @param unknown $a
     * @param unknown $b
     * @return boolean
     */
    private function SlowEquals($a, $b)
    {
        $diff = strlen($a) ^ strlen($b);
        for($i = 0; $i < strlen($a) && $i < strlen($b); $i++)
        {
            /**
             * 按位或运算   也就是如果两个字符串相等的话 ord($a[$i]) ^ ord($b[$i]); 的结果都为 0         而且$diff原来也为0      如果最终的$diff为0的话说明都相同 
             * @var unknown
             */
            $diff |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $diff === 0;
    }
    
    /**
     * 进行加密
     * @param unknown $algorithm
     * @param unknown $password
     * @param unknown $salt
     * @param unknown $count
     * @param unknown $key_length
     * @param string $raw_output
     */
    private function Pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false)
    {
        $algorithm = strtolower($algorithm);
        if(!in_array($algorithm, hash_algos(), true))
            trigger_error('PBKDF2 ERROR: Invalid hash algorithm.', E_USER_ERROR);
        if($count <= 0 || $key_length <= 0)
            trigger_error('PBKDF2 ERROR: Invalid parameters.', E_USER_ERROR);
    
        if (function_exists("hash_pbkdf2")) {
            // The output length is in NIBBLES (4-bits) if $raw_output is false!
            if (!$raw_output) {
                $key_length = $key_length * 2;
            }
            return hash_pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output);
        }
    
        $hash_length = strlen(hash($algorithm, "", true));
        $block_count = ceil($key_length / $hash_length);
    
        $output = "";
        for($i = 1; $i <= $block_count; $i++) {
            // $i encoded as 4 bytes, big endian.
            $last = $salt . pack("N", $i);
            // first iteration
            $last = $xorsum = hash_hmac($algorithm, $last, $password, true);
            // perform the other $count - 1 iterations
            for ($j = 1; $j < $count; $j++) {
                $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
            }
            $output .= $xorsum;
        }
    
        if($raw_output)
            return substr($output, 0, $key_length);
        else
            return bin2hex(substr($output, 0, $key_length));
    }
}