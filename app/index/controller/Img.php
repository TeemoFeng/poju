<?php
namespace app\index\controller;
use think\Controller;
use Tools\Captcha;
class Img extends controller
{
   public function img_cod()
   {
       $a= new captcha();
       return $a->img();
   }
}