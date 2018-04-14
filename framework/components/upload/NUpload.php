<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 18-4-3
 * Time: 下午9:30
 */

namespace framework\components\upload;

class NUpload extends Upload
{
    private $_prefx;
    private $_files = [];

    protected function init()
    {
        $this->_prefx = $this->getValueFromConf('prefx', 'nginx_file_');
        parent::init();
    }

    public function saves($files)
    {
        unset($this->_files);
        $this->_files = [];
        if (empty($files))
        {
            return false;
        }

        $this->getFiles($files);
        $paths = [];
        foreach ($this->_files as $file) {
            $path = $this->doSave($file);
            if ($path) {
                $paths[] = $path;
            } else {
                unlink($file['path']);
            }
        }

        return $paths;
    }

    public function save($file)
    {
        unset($this->_files);
        $this->_files = [];
        if (empty($file))
        {
            return false;
        }

        $this->getFiles($file, false);
        $paths = [];
        foreach ($this->_files as $file) {
            $path = $this->doSave($file);
            if ($path) {
                $paths[] = $path;
            } else {
                unlink($file['path']);
            }
        }

        return $paths[0] ?? '';
    }

    private function doSave($file)
    {
        if ($this->_maxSize > 0 && $file['size'] > $this->_maxSize)
        {
            return false;
        }

        $ext = $this->getFileExt($file['name']);
        if (!$ext)
        {
            return false;
        }

//        检测文件类型
        $mime = $file['content_type'];
        if (!(isset($this->_mime[$mime]) && in_array($this->_mime[$mime], $this->_accept)))
        {
//            进行严格检测
            return false;
        }

//        创建子目录
        $fileSavePath = $this->getSavePath($file['name'], $ext);
        //写入文件
        if ($this->moveUploadFile($file['path'], $fileSavePath))
        {
            $fileSavePath = str_replace(APP_ROOT, '', $fileSavePath);
            return $fileSavePath;
        }
        else
        {
            return false;
        }
    }

    private function getFiles($files, $multi = true, $is_first = true)
    {
        if ($is_first) {
            $prefxl = strlen($this->_prefx);
        }

        foreach ($files as $key => $item)
        {
            if ($is_first) {
                if (substr($key, 0, $prefxl) == $this->_prefx) {
                    if (is_array($item)) {
                        $this->getFiles($item, $multi,false);
                    }
                    if (!$multi) {
                        break;
                    }
                }
            } else if (is_array($item)) {
                $this->getFiles($item, $multi,false);
            } else {
                if (count($files) == 5)
                    $this->_files[] = $files;
                break;
            }
        }
    }
}