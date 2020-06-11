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
use think\captcha\Captcha;
use think\Db;

class Account extends WebBase
{
    //登录
    public function login()
    {
        if($this->request->isPost()){
            $postData = $this->request->post();
            //验证验证码
            if(empty($postData['code'])) {
                return ['code'=>2,'msg'=>'请输入图片验证码','model'=>'msg'];
            }
            $captcha = new Captcha();
            $check_code = $captcha->check($postData['code']);
            if (!$check_code) {
                return ['code'=>2,'msg'=>'图片验证码输入错误','model'=>'msg'];
            }
            $userModel = User::where('email|mobile|mk_id','=',$postData['account'])->find();
            if (empty($userModel)){
                return ['code'=>2,'msg'=>'账号不存在','model'=>'msg'];
            }
            if($userModel['status']=='2'){
                return ['code'=>2,'msg'=>'抱歉！您的账号被限制登录'];
            }
            if(!verifyMD5Code($postData['password'],$userModel['password'])){
                return ['code'=>2,'msg'=>'密码错误','model'=>'msg'];
            }
            if(isset($postData['remember'])){
               $this->keepLogin($userModel);
            }
            if(!empty($postData['oauth'])){
                OauthThird::where(['unionid'=>$postData['oauth']])->setField('uid',$userModel['id']);
            }
            session('user',$userModel);
            return json(['code'=>6,'msg'=>'登陆成功！','model'=>'msg','url'=>$postData['back']]);
        }else{
            $back_url = $this->request->param('b');
            $back_param = $this->request->param('c');
            if (empty($back_url)){
                $back_url = '/';
            }
            if (!empty($back_param)) {
                $back_url = $back_url . '/' . $back_param;
            }
            //获取区号列表
            $country_mobile_prefix = Db::name('country_mobile_prefix')->order('id ASC')->column('mobile_prefix','id');
            $this->assign('country_mobile_prefix', $country_mobile_prefix);
            $oauth = $this->request->get('oauth');
            $this->assign(['back'=>$back_url,'oauth'=>$oauth]);
            return $this->fetch('login2');
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
        $captcha = new Captcha();
        $check_code = $captcha->check($postData['code']);
        if (!$check_code) {
            return ['code'=>2,'msg'=>'图片验证码输入错误','model'=>'msg'];
        }
        $where['mobile'] = $postData['mobile'];
        $where['mobile_prefix'] = $postData['mobile_prefix'];
        $userModel = User::where($where)->find();
        if (empty($userModel)){
            return ['code'=>2,'msg'=>'账号不存在','model'=>'msg'];
        }
        if($userModel['status']=='2'){
            return ['code'=>2,'msg'=>'抱歉！您的账号被限制登录'];
        }

        if(!empty($postData['oauth'])){
            OauthThird::where(['unionid'=>$postData['oauth']])->setField('uid',$userModel['id']);
        }
        session('user',$userModel);
        return json(['code'=>6,'msg'=>'登陆成功！','model'=>'msg','url'=>$postData['back']]);
    }

    //注册
    public function register()
    {
        $user = new User();
        if($this->request->isPost()){
            $smsCode = session('mobileCode');

            $postData = $this->request->post();
            if (empty($postData[''])) {
                return ['code'=>2,'msg'=>'请先同意隐私协议！','model'=>'msg'];
            }

            if ($smsCode != $postData['sms_code']) {
                return ['code'=>2,'msg'=>'手机验证码不正确！','model'=>'msg'];
            }
            if(!$user->isNotReg($postData['email'],'email')) {
                return ['code'=>2,'msg'=>'该邮箱已被注册！','model'=>'msg'];
            }
            if(!$user->isNotReg($postData['mk_id'],'mk_id')) {
                return ['code'=>2,'msg'=>'该账号已被注册！','model'=>'msg'];
            }
            if(!$user->isNotReg($postData['mobile'],'mobile', $postData['mobile_prefix'])) {
                return ['code'=>2,'msg'=>'该手机号已被注册！','model'=>'msg'];
            }
            $postData['password'] = generateMD5WithSalt($postData['password']);
            $postData['status'] = 0;
            $postData['tid'] = $postData['direction'];
            $postData['nickname'] = $postData['mk_id'];
            if(!empty($postData['oauth'])){
               $oauthModel = OauthThird::get(['unionid'=>$postData['oauth']]);
               $postData['nickname'] = $oauthModel['nickname'];
               $imgModel = getImage($oauthModel['avatar'],'./upload/avatar/'.date('Y-m').'/',$oauthModel['unionid']);
               $postData['avatar'] = $imgModel['save_path'];
            }
            User::create($postData,true);
            if(!empty($postData['oauth'])){
                $userModel = User::get(['mk_id'=>$postData['mk_id']]);
                OauthThird::update(['uid'=>$userModel['id']],['unionid'=>$postData['oauth']],true);
                session('user',$userModel);
                $this->keepLogin($userModel);
                if($userModel['tid']==0){
                    return json(['code'=>1,'msg'=>'账号已注册成功!','act'=>url('index/User/infocenter'),'model'=>'msg']);
                }else{
                    return json(['code'=>1,'msg'=>'账号已注册成功!','act'=>url('index/Profile/setting'),'model'=>'msg']);
                }
            }
            return json(['code'=>1,'msg'=>'账号已注册成功!','act'=>url('index/Account/login'),'model'=>'msg']);
        }else{
            //获取区号列表
            $country_mobile_prefix = Db::name('country_mobile_prefix')->order('id ASC')->column('mobile_prefix','id');
            $this->assign('country_mobile_prefix', $country_mobile_prefix);
            $oauth = $this->request->get('oauth');
            $this->assign(['oauth'=>$oauth]);
            return $this->fetch();
        }
    }
    //注册检测账号
    public function check_account()
    {
        $name = $this->request->param('f');
        $val = $this->request->param($name);
        $user = new User();
        return $user->isNotReg($val,$name);
    }

    //登录检测账号
    public function check_account2()
    {
        $name = $this->request->param('f');
        $val = $this->request->param($name);
        $user = new User();
        $res = $user->isNotReg($val,$name);
        return $res ? false : true;
    }

    //找回密码
    public function password_find()
    {
       if($this->request->isPost()){
            $postData = $this->request->post();
            if($postData['mobile_code']!=session('mobileCode')){
                return ['code'=>2,'msg'=>'手机验证码输入错误！'];
            }
            $mobile = session('fd_mobile');
            $model = User::get(['mobile'=>$mobile,'mobile_prefix' => $postData['mobile_prefix']]);
            if (empty($model)) {
                return ['code'=>2, 'msg'=>'您尚未注册，请先注册'];
            }
            if (empty($postData['password'])) {
                return ['code'=>2, 'msg'=>'请填写您要设置的密码'];
            }
            $password = generateMD5WithSalt($postData['password']);

            User::update(['password'=>$password],['mobile'=>$mobile, 'mobile_prefix' => $postData['mobile_prefix']],true);

            if($model['tid']==2){
                SysAdmin::update(['password'=>$password],['uid'=>$model['id']],true);
            }

            return ['code'=>1,'msg'=>'密码已修改！'];
        }else{
           //获取区号列表
           $country_mobile_prefix = Db::name('country_mobile_prefix')->order('id ASC')->column('mobile_prefix','id');
           $this->assign('country_mobile_prefix', $country_mobile_prefix);
            return $this->fetch();
        }
    }
    //注销
    public function logout()
    {
        cookie('u', null);
        session('user', null);
        return json(['code'=>1,'msg'=>'注销成功！','url'=>'/']);
    }
    //更新用戶信息
    public function updateInfo()
    {

        $postData = $this->request->post();
        if (empty($postData)) {
            return ['code'=>2,'msg'=>'请选择更新内容！','model'=>'msg'];
        }
        $user = new User();

        if (!empty($postData['mk_id'])) {
            if (empty($postData['mk_id'])) {
                return ['code'=>2,'msg'=>'用户名不能为空！','model'=>'msg'];
            }
            if(!$user->isNotReg($postData['mk_id'],'mk_id')) {
                return ['code'=>2,'msg'=>'该账号已被注册！','model'=>'msg'];
            }

        }
        if(isset($postData['mobile'])){
            if($postData['sms_code']!=session('mobileCode')){
                return ['code'=>2,'msg'=>'手机验证码输入错误！', 'model'=>'msg'];
            }
            if (empty($postData['mobile'])) {
                return ['code'=>2,'msg'=>'手机号不能为空！', 'model'=>'msg'];
            }
            if(!$user->isNotReg($postData['mobile'],'mobile', $postData['mobile_prefix'])) {
                return ['code'=>2,'msg'=>'该手机号已被注册！','model'=>'msg'];
            }
        }
        if(isset($postData['email'])){
            if($postData['email_code']!=session('emailCode')){
                return ['code'=>2,'msg'=>'邮件验证码输入错误！', 'model'=>'msg'];
            }
            if(empty($postData['email'])) {
                return ['code'=>2,'msg'=>'邮箱不能为空！'];
            }
            if(!$user->isNotReg($postData['email'],'email')) {
                return ['code'=>2,'msg'=>'该邮箱已被注册！','model'=>'msg'];
            }
        }
        if(isset($postData['password'])){
            if(!verifyMD5Code($postData['oldpwd'],$this->UserInfo['password'])){
                return ['code'=>5,'msg'=>'原始密码输入错误错误', 'model'=>'msg'];
            }
            if (empty($postData['password'])) {
                return ['code'=>2, 'msg'=>'新密码不能为空', 'model'=>'msg'];
            }
            $postData['password'] = generateMD5WithSalt($postData['password']);
        }

        User::update($postData,['id'=>$this->UserInfo['id']],true);
        session('user', User::get($this->UserInfo['id']));
        return ['code'=>1,'msg'=>'保存成功！'];
    }

    public function relation()
    {
        $oauth = session('oauth');
        $this->assign('model',$oauth);
        return $this->fetch();
    }


    /***只返回页面***/
    //换手机
    public function changeMobile()
    {
        //获取区号列表
        $country_mobile_prefix = Db::name('country_mobile_prefix')->order('id ASC')->column('mobile_prefix','id');
        $this->assign('country_mobile_prefix', $country_mobile_prefix);
        $this->assign('mobile',$this->UserInfo['mobile']);
        return $this->fetch();
    }
    //换邮箱
    public function changeEmail()
    {
        $this->assign('email',$this->UserInfo['email']);
        return $this->fetch();
    }
    //换密码
    public function changePassword()
    {
        return $this->fetch();
    }
    //改名字
    public function changeName()
    {
        $this->assign('name',$this->UserInfo['name']);
        return $this->fetch();
    }
    //改昵称
    public function changeNickname()
    {
        $this->assign('nickname',$this->UserInfo['nickname']);
        return $this->fetch();
    }
    //改公司
    public function changeCompany()
    {
        $this->assign('company',$this->UserInfo['company']);
        return $this->fetch();
    }
    //改职位
    public function changePosition()
    {
        $this->assign('position',$this->UserInfo['position']);
        return $this->fetch();
    }
    //改简介
    public function changeIntro()
    {
        $this->assign('intro',$this->UserInfo['intro']);
        return $this->fetch();
    }
    //改MKID
    public function changeMkid()
    {
        $name = '机构名称';
        if($this->UserInfo['tid']==0){
            $name='用户名';
        }
        $this->assign('mk_id',$this->UserInfo['mk_id']);
        $this->assign('name',$name);
        return $this->fetch();
    }
    /***END***/

    //生成验证码
    public function verify()
    {
        $config = [
            // 验证码字符集合
            'codeSet' => '2345678abcdefghijklmnopqrstuvwxyz',
            // 验证码字体大小(px)
            'fontSize' => 14,
            // 是否画混淆曲线
            'useCurve' => false,
            // 验证码图片高度
            'imageH' => 45,
            // 验证码图片宽度
            'imageW' => 130,
            // 验证码位数
            'length' => 4,
            // 验证成功后是否重置        
            'reset' => true
        ];

        $captcha = new Captcha($config);
        return $captcha->entry();

    }

    //获取设置的隐私协议
    public function privacy()
    {
        $sc = new SCModel();
        $sys_config = $sc->column("value","name");
        $content = '';
        if (isset($sys_config['treaty']) && !empty($sys_config['treaty'])) {
            $content = $sys_config['treaty'];
        }
        $this->assign('content', $content);
        return $this->fetch('privacy');

    }

    public function loginDialog()
    {
        return $this->fetch();
    }
}