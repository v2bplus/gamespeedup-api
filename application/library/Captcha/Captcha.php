<?php
namespace Captcha;

class Captcha
{
    protected $code;
    protected $img;
    protected $width = 100;
    protected $height = 25;
    protected $font = '';

    public function __construct($code = null, $width = 100, $height = 25)
    {
        $this->width = $width;
        $this->height = $height;
        $this->code = (string) $code;
        $this->font = __DIR__.'/font/ABeeZee.ttf';
    }

    public function generate()
    {
        $this->img = imagecreatetruecolor($this->width, $this->height);
        $white = imagecolorallocate($this->img, 250, 250, 255);
        imagefill($this->img, 0, 0, $white);
        $xBase = intval($this->width / 8);
        $yBase = intval($this->height / 8);
        // 添加干扰线和文字
        $offset = 0;
        $len = strlen($this->code);
        for ($i = 0; $i < $len; ++$i) {
            $color = imagecolorallocate($this->img, mt_rand(0, 100), mt_rand(20, 120), mt_rand(50, 150));
            $offset += mt_rand(12, $xBase * 2);
            // imagestring($this->img, 4, $offset, mt_rand(0, $yBase * 6), $this->code[$i], $color);
            imagettftext($this->img, mt_rand(14, 20), mt_rand(-20, 20), $offset, max(20, mt_rand($yBase * 3, $yBase * 7)), $color, $this->font, $this->code[$i]);
            // 添加同色干扰弧线
            if ($i < 1) {
                // imageline($this->img, mt_rand(0, $xBase), mt_rand(0, $this->height), mt_rand($xBase * 6, $this->width), mt_rand($yBase, $this->height), $color);
                imagearc($this->img, mt_rand($xBase, $this->width), mt_rand(0, $yBase * 4), $this->width, $yBase * 4, mt_rand(0, 45), mt_rand(90, 200), $color);
            }
        }
        ob_clean();
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1970 09:00:00 GMT');
        header('Pragma: no-cache');
        header('Cache-control: private');
        header('Content-Type: image/png');
        imagepng($this->img);
        imagedestroy($this->img);
    }
}
