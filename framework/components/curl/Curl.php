<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/11/1
 * Time: 20:43
 */
namespace framework\components\curl;
use framework\base\Component;

class Curl extends Component
{
    private $_post;
    private $_retry;
    private $_option;
    private $_default;

    protected function init()
    {
        $this->_retry = $this->getValueFromConf('retry',0);
        $this->_default = array(
            'CURLOPT_TIMEOUT' => $this->getValueFromConf('timeout', 30),
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_SSL_VERIFYPEER' => $this->getValueFromConf('ssl', false)
        );
    }

    /**
     * 提交GET请求
     * @param string $url
     * @return array
     */
    public function get($url)
    {
        return $this->set('CURLOPT_URL', $url)->exec();
    }

    /**
     * 设置POST信息
     * @param array|string  $data
     * @param string        $value
     * @return $this
     */
    public function post($data, $value = '')
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->_post[$key] = $value;
            }
            unset($data);
        } elseif ($value) {
            $this->_post[$data] = $value;
        } else {
            $this->_post = $data;
        }
        return $this;
    }

    /**
     * 设置文件上传
     * @param string $field
     * @param string $path
     * @param string $type
     * @param string $name
     * @return $this
     */
    public function upload($field, $path, $type, $name)
    {
        $name = basename($name);
        if (class_exists('CURLFile')) {
            $this->set('CURLOPT_SAFE_UPLOAD', true);
            $file = curl_file_create($path, $type, $name);
        } else {
            $file = "@{$path};type={$type};filename={$name}";
        }
        return $this->post($field, $file);
    }

    /**
     * 提交POST请求
     * @param string $url
     * @return array
     */
    public function submit($url)
    {
        if (! $this->_post) {
            return false;
        }
        return $this->set('CURLOPT_URL', $url)->exec();
    }

    /**
     * 设置下载地址
     * @param string $url
     * @return $this
     */
    public function download($url, $savePath)
    {
        if (empty($url) || empty($savePath)) {
            return false;
        }
        $this->set('CURLOPT_URL', $url);
        $result = $this->exec();
        if ($result['error'] === 0) {
            $fp = @fopen($savePath, 'w');
            fwrite($fp, $result['body']);
            fclose($fp);
        }
        unset($result);
        return true;
    }

    /**
     * 配置Curl操作
     * @param array|string  $item
     * @param string        $value
     * @return $this
     */
    public function set($item, $value = '')
    {
        if (is_array($item)) {
            foreach($item as $key => &$value){
                $this->_option[$key] = $value;
            }
            unset($item);
        } else {
            $this->_option[$item] = $value;
        }
        return $this;
    }

    /**
     * 出错自动重试
     * @param int $times
     * @return $this
     */
    public function retry($times = 0)
    {
        $this->_retry = $times;
        return $this;
    }

    /**
     * 执行Curl操作
     * @param int $retry
     * @return array
     */
    private function exec($retry = 0)
    {

        // 初始化句柄
        $ch = curl_init();

        // 配置选项
        $options = array_merge($this->_default, $this->_option);
        foreach($options as $key => $val) {
            if (is_string($key)) {
                $key = constant(strtoupper($key));
            }
            curl_setopt($ch, $key, $val);
        }
        unset($options);

        // POST选项
        if ($this->_post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post_fields_build($this->_post));
        }

        // 运行句柄
        $body = curl_exec($ch);
        $info = curl_getinfo($ch);

        // 检查错误
        $errno = curl_errno($ch);
        if ($errno === 0 && $info['http_code'] >= 400) {
            $errno = $info['http_code'];
        }

        // 注销句柄
        curl_close($ch);

        // 自动重试
        if ($errno && $retry < $this->_retry) {
            $this->exec($retry + 1);
        }

        // 注销配置
        $this->_post     = null;
        $this->_retry    = $this->getValueFromConf('retry');
        $this->_option   = null;

        // 返回结果
        return array(
            'error'     => $errno ? 1 : 0,
            'message'   => $errno,
            'body'      => $body,
            'info'      => $info
        );
    }

    /**
     * 一维化POST信息
     * @param array  $input
     * @param string $pre
     * @return array
     */
    private function post_fields_build($input, $pre = null){
        if (is_array($input)) {
            $output = array();
            foreach ($input as $key => $value) {
                $index = is_null($pre) ? $key : "{$pre}[{$key}]";
                if (is_array($value)) {
                    $output = array_merge($output, $this->post_fields_build($value, $index));
                } else {
                    $output[$index] = $value;
                }
            }
            unset($input);
            return $output;
        }
        return $input;
    }

}