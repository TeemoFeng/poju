<?php
namespace Tools;
use think\Session;
class Captcha
{
   public function  img()
   {
       //画布大小
       $image = imagecreate(130, 40);
       imagecolorallocate($image, 255, 0, 0);
       $white = imagecolorallocate($image, 255, 255, 255);
       //$white_a=imagecolorallocatealpha($image, 185, 185, 185, 100);
       $codeSet='23456789abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
       //创建验证码
       $code = '';
       //$z = $codeSet[mt_rand(10, 35)];
       //imagettftext($image,25,0,mt_rand(10, 120), mt_rand(5, 35),$white_a,__DIR__."/../../public/static/backend/fonts/zoo.ttf",$z);
       for($i=0;$i<4;$i++)
       {
           $fs = 24;
           $x = $i * 32+ 10;
           $y = 30;
           $c = $codeSet[mt_rand(0, strlen($codeSet) - 1)];
           $code .= $c;
           imagettftext($image,$fs,mt_rand(-45,45),$x, $y,$white,__DIR__."/../../public/static/backend/fonts/code.ttf",$c);

       }
       ob_clean();
       //验证码记录到session
       Session::set("code",strtolower($code));
       //增加干扰元素点
       for ($i=0; $i <80 ; $i++) {
           imagesetpixel($image, mt_rand(0,130), mt_rand(0,40), $white);
       }
       //增加干扰线
       for ($i=0; $i <3 ; $i++) {
           imageline($image, mt_rand(0,130),mt_rand(0,40),mt_rand(0,130),rand(0,40), $white);
       }
       //输出到浏览器
       imagepng($image);
       $content = ob_get_clean();
       //关闭
       imagedestroy($image);
       return response($content, 200, ['Content-Length' => strlen($content)])->contentType('image/png');
    }
}