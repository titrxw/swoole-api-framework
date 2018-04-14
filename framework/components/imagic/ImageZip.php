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

namespace framework\components\imagic;

use framework\base\Component;

class ImageZip extends Component
{
    protected $_image;
    protected $_type;

    protected function init()
    {
        if (!extension_loaded('imagick')) {
            $this->triggerException(new \Exception('not support: imagick', 500));
        }
    }

    public function open($path)
    {
        $this->clear();
        $this->_image = new \Imagick ( $path );
        if ($this->_image) {
            $this->_type = strtolower ( $this->_image->getImageFormat () );
        }
        return $this;
    }


    public function resizeTo($width=0, $height=0, $isCrop = false)
    {
        if($width==0 && $height==0){
            return;
        }

        $color = '';// 'rgba(255,255,255,1)';
        $size = $this->_image->getImagePage ();
        //原始宽高
        $src_width = $size ['width'];
        $src_height = $size ['height'];

        //按宽度缩放 高度自适应
        if($width!=0 && $height==0){
            if($src_width>$width){
                $height = intval($width*$src_height/$src_width);

                if ($this->_type == 'gif') {
                    $this->resizeGif($width, $height, $isCrop);
                }else{
                    $this->_image->thumbnailImage ( $width, $height, true );
                }
            }
            return $this;
        }
        //按高度缩放 宽度自适应
        if($width==0 && $height!=0){
            if($src_height>$height){
                $width = intval($src_width*$height/$src_height);

                if ($this->_type == 'gif') {
                    $this->resizeGif($width, $height, $isCrop);
                }else{
                    $this->_image->thumbnailImage ( $width, $height, true );
                }
            }

            return $this;
        }

        //缩放的后的尺寸
        $crop_w = $width;
        $crop_h = $height;

        //缩放后裁剪的位置
        $crop_x = 0;
        $crop_y = 0;

        if(($src_width/$src_height) < ($width/$height)){
            //宽高比例小于目标宽高比例  宽度等比例放大      按目标高度从头部截取
            $crop_h = intval($src_height*$width/$src_width);
            //从顶部裁剪  不用计算 $crop_y
        }else{
            //宽高比例大于目标宽高比例   高度等比例放大      按目标宽度居中裁剪
            $crop_w = intval($src_width*$height/$src_height);
            $crop_x = intval(($crop_w-$width)/2);
        }

        if ($this->_type == 'gif') {
            $this->resizeGif($crop_w, $crop_h, $isCrop, $width, $height,$crop_x, $crop_y);
        } else {
            $this->_image->thumbnailImage ( $crop_w, $crop_h, true );
            if ($isCrop)
                $this->_image->cropImage($width, $height,$crop_x, $crop_y);
        }
        return $this;
    }

    private function resizeGif($t_w, $t_h, $isCrop=false, $c_w=0, $c_h=0, $c_x=0, $c_y=0)
    {
        $dest = new \Imagick();
        $color_transparent = new \ImagickPixel("transparent");   //透明色
        foreach($this->_image as $img){
            $page = $img->getImagePage();
            $tmp = new \Imagick();
            $tmp->newImage($page['width'], $page['height'], $color_transparent, 'gif');
            $tmp->compositeImage($img, \Imagick::COMPOSITE_OVER, $page['x'], $page['y']);

            $tmp->thumbnailImage ( $t_w, $t_h, true );
            if($isCrop){
                $tmp->cropImage($c_w, $c_h, $c_x, $c_y);
            }

            $dest->addImage($tmp);
            $dest->setImagePage($tmp->getImageWidth(), $tmp->getImageHeight(), 0, 0);
            $dest->setImageDelay($img->getImageDelay());
            $dest->setImageDispose($img->getImageDispose());

        }
        $this->clear();
        $this->_image = $dest;
    }

    // 添加水印图片
    public function addWatermark($path, $x = 0, $y = 0)
    {
        $watermark = new \Imagick ( $path );
        $draw = new \ImagickDraw ();
        $draw->composite ( $watermark->getImageCompose (), $x, $y, $watermark->getImageWidth (), $watermark->getimageheight (), $watermark );

        if ($this->_type == 'gif') {
            $canvas = new \Imagick ();
            $images = $this->_image->coalesceImages ();
            foreach ( $images as $frame ) {
                $img = new \Imagick ();
                $img->readImageBlob ( $frame );
                $img->drawImage ( $draw );

                $canvas->addImage ( $img );
                $canvas->setImageDelay ( $img->getImageDelay () );
            }
            $images->destroy ();
            $this->_image->destroy ();
            $this->_image = $canvas;
        } else {
            $this->_image->drawImage ( $draw );
        }
        return $this;
    }

    // 添加水印文字
    public function addText($text, $x = 0, $y = 0, $angle = 0, $style = array())
    {
        $draw = new \ImagickDraw ();
        if (isset ( $style ['font'] ))
            $draw->setFont ( $style ['font'] );
        if (isset ( $style ['font_size'] ))
            $draw->setFontSize ( $style ['font_size'] );
        if (isset ( $style ['fill_color'] ))
            $draw->setFillColor ( $style ['fill_color'] );
        if (isset ( $style ['under_color'] ))
            $draw->setTextUnderColor ( $style ['under_color'] );

        if ($this->_type == 'gif') {
            foreach ( $this->_image as $frame ) {
                $frame->annotateImage ( $draw, $x, $y, $angle, $text );
            }
        } else {
            $this->_image->annotateImage ( $draw, $x, $y, $angle, $text );
        }
        return $this;
    }

    public function saveTo($path)
    {
        //压缩图片质量
        $this->_image->setImageFormat('JPEG');
        $this->_image->setImageCompression(\Imagick::COMPRESSION_JPEG);
        $a = $this->_image->getImageCompressionQuality() * 0.75;
        if ($a == 0) {
            $a = 75;
        }
        $this->_image->setImageCompressionQuality($a);
        $this->_image->stripImage();

        if ($this->_type == 'gif') {
            $this->_image->writeImages ( $path, true );
        } else {
            $this->_image->writeImage ( $path );
        }

        $this->clear();
        unset($this->_image);
    }

    private function clear()
    {
        if ($this->_image) {
            $this->_image->clear();
            $this->_image->destroy ();
        }
    }

    public function __destruct()
    {
        $this->clear();
    }
}