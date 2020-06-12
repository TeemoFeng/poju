<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-11-07
 * Time: 18:25
 */
namespace app\index\controller;
use app\backstage\model\SysConfig as SCModel;
use app\backstage\model\User;
use app\index\model\OauthThird;
use app\backstage\model\SysAdmin;
use think\Db;
use think\Request;
use think\Session;
use Tools\Captcha;

class Account extends WebBase
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->db_app = Db::connect('database_morketing');
        $this->user = $this->db_app->name('user');
    }

    //登录
    public function login()
    {
        if($this->request->isPost()){
            $postData = $this->request->post();
            //验证验证码
            if(empty($postData['code'])) {
                return ['code'=>2,'msg'=>'请输入图片验证码','model'=>'msg'];
            }
            if ($postData['code'] != Session::get("code")) {
                return ['code'=>2,'msg'=>'图片验证码输入错误','model'=>'msg'];
            }

            $userModel = $this->user->where('email|mobile|mk_id','=',$postData['account'])->find();
            if (empty($userModel)){
                return ['code'=>2,'msg'=>'账号不存在','model'=>'msg'];
            }
            if($userModel['status']=='2'){
                return ['code'=>2,'msg'=>'抱歉！您的账号被限制登录'];
            }
            if(!verifyMD5Code($postData['password'],$userModel['password'])){
                return ['code'=>2,'msg'=>'密码错误','model'=>'msg'];
            }

            session('userInfo',$userModel);
            return json(['code'=>6,'msg'=>'登陆成功！','model'=>'msg']);
        }
    }

    //短信登录
    public function mobileLogin()
    {
        $postData = $this->request->post();
        //校验手机验证码
        if (empty($postData['sms_code'])) {
            return ['code'=>2,'msg'=>'请输入短信验证码','model'=>'msg'];
        }
        if($postData['sms_code'] != session('mobileCode')){
            return ['code'=>2,'msg'=>'手机验证码输入错误！'];
        }

        //验证验证码
        if(empty($postData['code'])) {
            return ['code'=>2,'msg'=>'请输入图片验证码','model'=>'msg'];
        }
        if ($postData['code'] != Session::get("code")) {
            return ['code'=>2,'msg'=>'图片验证码输入错误','model'=>'msg'];
        }

        $where['mobile'] = $postData['mobile'];
        $where['mobile_prefix'] = $postData['mobile_prefix'];
        $userModel = $this->user->where($where)->find();
        if (empty($userModel)){
            return ['code'=>2,'msg'=>'账号不存在','model'=>'msg'];
        }
        if($userModel['status']=='2'){
            return ['code'=>2,'msg'=>'抱歉！您的账号被限制登录'];
        }

        session('userInfo',$userModel);
        return json(['code'=>6,'msg'=>'登陆成功！','model'=>'msg']);
    }


    //注册检测账号
    public function check_account()
    {
        $name = $this->request->param('f');
        $val = $this->request->param($name);

         $where[$name] = $val;
        if (!empty($mobile_prefix)) {
            $where['mobile_prefix'] = $mobile_prefix;
        }
        $userModel = $this->user->where($where)->find();
        if (empty($userModel)) {
            return true;
        }else{
            return false;
        }
    }



}