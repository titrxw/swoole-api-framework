<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 17-9-20
 * Time: 下午10:14
 */
namespace framework\components\upload;

use framework\base\Component;

class Upload extends Component
{
    protected $_baseDir;
    protected $_accept;
    protected $_maxSize;
    protected $_nameType; // md5  time
    protected $_deep;
    protected $_mime = array(
        'image/jpeg' => 'jpg',
        'image/bmp' => 'bmp',
        'image/x-icon' => 'ico',
        'image/gif' => 'gif',
        'image/png' => 'png',
        'application/x-tar' => 'tar',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/pdf' => 'pdf',
        'application/x-shockwave-flash' => 'swf',
        'application/x-zip-compressed' => 'zip',
        'application/gzip' => 'gzip'
    );

    protected function init()
    {
        $this->_baseDir = APP_ROOT . \getModule() . '/' . $this->getValueFromConf('baseDir','runtime/upload');
        $this->_accept = $this->getValueFromConf('accept', []);
        $this->_maxSize = $this->getValueFromConf('maxSize', get_cfg_var ("upload_max_filesize")?get_cfg_var ("upload_max_filesize"): 0);
        $this->_nameType = $this->getValueFromConf('nameType', 'time');
        if ($this->_nameType !== 'md5')
            return true;

        $this->_deep = $this->getValueFromConf('deep', 2);
        if ($this->_deep > 5)
        {
            $this->_deep = 5;
        }
    }

    protected function getSavePath($name, $ext)
    {
        if (!file_exists($this->_baseDir))
        {
            $dirs = explode('/', $this->_baseDir);
            $path = '';
            foreach ($dirs as $item) {
                $path .= $item;
                if (!file_exists($path)) {
                    mkdir($path, 0755);
                }
                $path .= '/';
            }
        }
        switch ($this->_nameType)
        {
            case 'md5':
                $name = mt_rand() . $name;
                $name = md5($name . SYSTEM_WORK_ID . microtime());
                $length = strlen($name);
                $particle = ceil($length / $this->_deep);
                $currentPath = '';
                for ($i=0; $i<$this->_deep;$i++)
                {
                    $tmpDir = substr($name, $i*$particle, $particle);
                    $currentPath .= $tmpDir . '/';
                    if (!file_exists($this->_baseDir . '/' . $currentPath))
                    {
                        mkdir($this->_baseDir . '/' . $currentPath, 0755);
                    }
                }
                return $this->_baseDir . '/' . $currentPath . '/' . $name . '.' . $ext;
                break;
            case 'time':
            default:
                $name = mt_rand() . $name;
                $name = md5($name . SYSTEM_WORK_ID . microtime());
                $subPath = date('Ymd');
                if (!file_exists($this->_baseDir . '/' . $subPath))
                {
                    mkdir($this->_baseDir . '/' . $subPath, 0755);
                }
                return $this->_baseDir . '/' . $subPath . '/' . $name . '.' . $ext;
                break;
        }
    }

    protected function getFileExt($file)
    {
        $s = strrchr($file, '.');
        if ($s === false)
        {
            return false;
        }
        return strtolower(trim(substr($s, 1)));
    }

    protected function moveUploadFile($tmpfile, $newfile)
    {
//            return move_uploaded_file($tmpfile, $newfile);    不支持
        if (rename($tmpfile, $newfile) === false)
        {
            return false;
        }
        return chmod($newfile, 0666);
    }

    protected function securityVeritify($filePath, $mime)
    {
        return true;
    }

    public function save($name)
    {
        //检查请求中是否存在上传的文件
        if (empty($_FILES[$name]))
        {
            return false;
        }

//        检测文件大小
        $fileSize = filesize($_FILES[$name]['tmp_name']);
        if ($this->_maxSize > 0 && $fileSize > $this->_maxSize)
        {
            return false;
        }

        $ext = $this->getFileExt($_FILES[$name]['name']);
        if (!$ext)
        {
            return false;
        }

//        检测文件类型
        $mime = $_FILES[$name]['type'];
        if (!(isset($this->_mime[$mime]) && in_array($this->_mime[$mime], $this->_accept)))
        {
            return false;
        }

//            进行严格检测
        if (!$this->securityVeritify($_FILES[$name]['tmp_name'], $mime)) {
            return false;
        }

//        创建子目录
        $fileSavePath = $this->getSavePath($_FILES[$name]['name'], $ext);

        //写入文件
        if ($this->moveUploadFile($_FILES[$name]['tmp_name'], $fileSavePath))
        {
            return str_replace(APP_ROOT, '', $fileSavePath);
        }
        else
        {
            return false;
        }
    }

    public function saveAll()
    {
        $return = [];
        if($_FILES)
        {
            foreach($_FILES as $k=>$f)
            {
                $return[] = $this->save($k);
            }
        }
        return $return;
    }
}
