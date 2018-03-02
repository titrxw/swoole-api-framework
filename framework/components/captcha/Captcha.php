<?php
/**
 * Created by PhpStorm.
 * User: rxw
 * Date: 2017/10/15
 * Time: 17:07
 */
namespace framework\components\captcha;
use framework\base\Component;

class Captcha extends Component
{
    /**
     * 验证码中的字符串
     * @var unknown
     */
    protected $_code;
    /**
     * 验证码高度
     * @var unknown
     */
    protected $_height;

    /**
     * 验证码宽度
     * @var unknown
     */
    protected $_width;
    /**
     * 验证码中的字符个数
     * @var unknown
     */
    protected $_num;

    /**
     * 图片
     * @var unknown
     */
    protected $_img;

    protected $_type;

    /**
     * 初始化变量
     * @param unknown $height
     * @param unknown $width
     * @param number $num
     */

    protected function init()
    {
        $this->_height = $this->getValueFromConf('height', '0');
        $this->_width = $this->getValueFromConf('width', '0');
        $this->_num = $this->getValueFromConf('num', '0');
        $this->_type = $this->getValueFromConf('type', 'png');
    }

    public function GetCode()
    {
        return $this->_code;
    }

    //输出图像
    public function send()
    {

        if (!empty($this->_img))
        {
            imagedestroy($this->_img);
        }
        //创建背景 (颜色， 大小， 边框)
        $this->CreateBack();

        $this->CreateCode();
        //画字 (大小， 字体颜色)
        $this->OutString();

        //干扰元素(点， 线条)

        $this->SetDisturbColor();
        //输出图像
        ob_start();
        $this->PrintImg();
        return ob_get_clean();
    }

    //创建背景
    private function CreateBack()
    {
        //创建资源
        $this->_img = imagecreatetruecolor($this->_width, $this->_height);
        //设置随机的背景颜色
        $bgcolor = imagecolorallocate($this->_img, rand(225, 255), rand(225, 255), rand(225, 255));
        //设置背景填充
        imagefill($this->_img, 0, 0, $bgcolor);
        //画边框
        $bordercolor = imagecolorallocate($this->_img, 0, 0, 0);

        imagerectangle($this->_img, 0, 0, $this->_width-1, $this->_height-1, $bordercolor);
    }

    //画字
    private function OutString()
    {
        for($i=0; $i<$this->_num; $i++)
        {
            $color= imagecolorallocate($this->_img, rand(0, 128), rand(0, 128), rand(0, 128));

            $fontsize=rand(17,20); //字体大小

            $x = 3+($this->_width/$this->_num)*$i; //水平位置
            $y = rand(0, imagefontheight($fontsize)-3) + $this->_height/3;

            //画出每个字符
            imagechar($this->_img, $fontsize, $x, $y, $this->_code[$i], $color);
        }
    }

    //设置干扰元素
    private function SetDisturbColor()
    {
        //加上点数
        for($i=0; $i<100; $i++)
        {
            $color= imagecolorallocate($this->_img, rand(0, 255), rand(0, 255), rand(0, 255));
            imagesetpixel($this->_img, rand(1, $this->_width-2), rand(1, $this->_height-2), $color);
        }

        //加线条
        for($i=0; $i<10; $i++)
        {
            $color= imagecolorallocate($this->_img, rand(0, 255), rand(0, 128), rand(0, 255));
            imagearc($this->_img,rand(-10, $this->_width+10), rand(-10, $this->_height+10), rand(30, 300), rand(30, 300), 55,44, $color);
        }
    }

    //输出图像
    private function PrintImg()
    {
        if (imagetypes() & IMG_GIF && $this->_type === 'git')
        {
            $this->getComponent(SYSTEM_APP_NAME, 'response')->contentType('gif');
            imagegif($this->_img);
        }
        elseif (function_exists("imagejpeg") && IMG_JPG && $this->_type === 'jpg')
        {
            $this->getComponent(SYSTEM_APP_NAME, 'response')->contentType('jpg');
            imagejpeg($this->_img);
        }
        elseif (imagetypes() & IMG_PNG && $this->_type === 'png')
        {
            $this->getComponent(SYSTEM_APP_NAME, 'response')->contentType('png');
            imagepng($this->_img);
        }
    }

    //生成验证码字符串
    private function CreateCode()
    {
        $codes = "3456789abcdefghijkmnpqrstuvwxyABCDEFGHIJKLMNPQRSTUVWXY";

        $this->_code = "";

        for($i=0; $i < $this->_num; $i++)
        {
            $this->_code .=$codes{rand(0, strlen($codes)-1)};
        }
    }
}