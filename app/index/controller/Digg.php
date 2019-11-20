<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-09-02
 * Time: 16:53
 */
namespace app\index\controller;
use Tools\WxShare;
class Digg extends WebBase
{
    public function getWxShareSign()
    {
        $url= $this->request->param('url');
        $wxShareData = WxShare::instance()->getShareParams($url);
        return $wxShareData;
    }
}