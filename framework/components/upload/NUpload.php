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
    public function save($file, $pass_args = false)
    {
        if (empty($file))
        {
            return false;
        }

//        检测文件大小
        if ($this->_maxSize > 0 && $file['file_size'] > $this->_maxSize)
        {
            return false;
        }

        $ext = $this->getFileExt($file['file_name']);
        if (!$ext)
        {
            return false;
        }

//        检测文件类型
        $mime = $file['file_content_type'];
        if (!(isset($this->_mime[$mime]) && in_array($this->_mime[$mime], $this->_accept)))
        {
//            进行严格检测
            return false;
        }

//        创建子目录
        $fileSavePath = $this->getSavePath($file['file_name'], $ext);

        //写入文件
        if ($this->moveUploadFile($file['file_path'], $fileSavePath))
        {
            $fileSavePath = str_replace(APP_ROOT, '', $fileSavePath);
            if ($pass_args) {
                unset($file['file_name'], $file['file_content_type'], $file['file_path'], $file['file_md5'], $file['file_size']);
                $file['save_path'] = $fileSavePath;
                return $file;
            }

            return $fileSavePath;
        }
        else
        {
            return false;
        }
    }
}