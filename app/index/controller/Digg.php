<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-09-02
 * Time: 16:53
 */
namespace app\index\controller;
use app\backstage\model\User;
use think\Db;
use Tools\Alisms;
use Tools\WxShare;
class Digg extends WebBase
{
    public function getWxShareSign()
    {
        $url= $this->request->param('url');
        $wxShareData = WxShare::instance()->getShareParams($url);
        return $wxShareData;
    }


    //发送短信
    public function sendSms($mobile, $mobile_prefix = '')
    {
        $code = mt_rand(100000,999999);
        session('mobileCode',$code);
        $alisms = new Alisms();
        $alisms = $alisms->param(['code'=>$code]);
        if (empty($mobile_prefix) || $mobile_prefix == '86') {
            $alisms = $alisms->template('SMS_151771231');
        } else {
            $alisms = $alisms->template('SMS_181860050'); //国际短信模板
            $mobile = $mobile_prefix.$mobile;

        }
        $alisms = $alisms->mobile($mobile);
        $res = $alisms->send();
        return $res? json(['code'=>1,'msg'=>'验证码已发送','error'=>$alisms->getError()]):json(['code'=>2,'msg'=>'验证码发送失败！','error'=>$alisms->getError()]);
    }

    //登录发送验证码
    public function login_sms()
    {
        $mobile_prefix = $this->request->post('mobile_prefix');
        $mobile_prefix = $mobile_prefix ?: '86';
        $mobile = $this->request->post('mobile');
        $this->db_app = Db::connect('database_morketing');
        $user = $this->db_app->name('user')->where(['mobile' => $mobile])->find();
        if(!$user)
        {
            return json(['code'=>2,'msg'=>'该手机号尚未注册,请先注册在登录！','model'=>'msg']);
        }

        return $this->sendSms($mobile, $mobile_prefix);
    }

}