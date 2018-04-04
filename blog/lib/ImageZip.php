<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 18-4-4
 * Time: 下午8:47
 */
//https://blog.csdn.net/sxhong/article/details/42201265

/**
 * 需要添加imagic扩展
 */

namespace blog\lib;

use framework\base\Component;

class ImageZip extends Component
{
    public function resize($src, $width=320, $height=320, $dst = '',  $crop=false)
    {
        $imagick = new \Imagick(APP_ROOT . $src);

        $w = $imagick->getImageWidth();
        $h = $imagick->getImageHeight();

        if ($w > $width || $h > $height) {
            if ($crop) {
                $imagick->cropThumbnailImage($width, $height);
            } else {
                $imagick->resizeImage($width, $height, \Imagick::FILTER_CATROM, 1, true);
            }
        }

        $imagick->setImageFormat('JPEG');
        $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
        $a = $imagick->getImageCompressionQuality() * 0.75;

        if ($a == 0) {
            $a = 75;
        }

        $imagick->setImageCompressionQuality($a);
        $imagick->stripImage();

        if (empty($dst)) {
            $imgName = strrchr($src, '/');
            $dir = substr($src, 0, strrpos($src, '/'));
            $ext = strrchr($imgName, '.');
            $imgName = strchr($imgName,'.',true);
            $dst = $dir . $imgName . $width . 'x' . $height . $ext;
        }
        $imagick->writeImage($dst);
        $imagick->clear();
        $imagick->destroy();
        $dst = str_replace(APP_ROOT, '', $dst);

        unset($imagick);
        return $dst;
    }
}