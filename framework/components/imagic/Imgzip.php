<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 18-4-9
 * Time: 下午10:38
 */
namespace framework\components\imagic;

class Imgzip extends ImageZip
{
    public function resize($path, $width=320, $height=320, $dst = '',  $crop=false)
    {
        $fpath = APP_ROOT.'/'.$path;
        if (!file_exists($fpath) || !is_readable($fpath)) {
            return false;
        }
        if (empty($dst)) {
            $imgName = strrchr($path, '/');
            $dir = substr($path, 0, strrpos($path, '/'));
            $ext = strrchr($imgName, '.');
            $imgName = strchr($imgName,'.',true);
            $dst = $dir . $imgName . $width . 'x' . $height . $ext;
        }
        if (file_exists(APP_ROOT.'/'.$dst)) {
            return $dst;
        }
        try
        {
            $this->open($path)->resizeTo($width,$height,$crop)->saveTo(APP_ROOT.'/'.$dst);
        } catch (\Throwable $e) {
            $this->handleException($e);
            return false;
        }

        return $dst;
    }
}