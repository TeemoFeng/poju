<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/16
 * Time: 14:47
 */

namespace app\api\controller;

use app\api\library\ApiBase;
use app\api\library\Token;
use think\Db;
use think\Log;
use think\Request;
use think\Session;
use Tools\Alisms;
use app\api\model\User as UserModel;

/**
 * 用户
 * Class User
 * @package app\api\controller
 */
class User extends ApiBase
{
    /**
     * 无需登录的方法
     */
    protected $noNeedLogin = ['login', 'sendCode', 'register', 'getVerify', 'verify', 'pwdLogin', 'messageLogin', 'mobilePrefixList'];


    /***
     * Action 登录接口
     * @author ywf
     * @license /api/user/login POST
     * @para string account   用户名/手机号/邮箱|Y
     * @para string password   密码|Y
     * @field string code   1:成功;0:失败
     * @field string msg    code=0:1.账号不存在。2.抱歉！您的账号被限制登录,3.密码错误,code=1:登录成功
     * @field string data.userInfo    用户信息
     * @field string data.token    token值
     * @jsondata {"code":"dsfsfdsdf","encryptedData":"CiyLU1Aw2KjvrjMdj8YKliAjtP4gsM","iv":"r7BXXKkLb8qrSNn05n0qiA=="}
     * @jsondatainfo {"code":1,"msg":"登录成功","time":"1572510481","data":{"userInfo":{},"token":"sdfsdfsdfsdf"}}
     */
    public function login()
    {
        $postData = $this->request->post();
        if (empty($postData['account'])) {
            $this->error('请输入账号');
        }
        if (empty($postData['password'])) {
            $this->error('请输入密码');
        }
        //查询morketing中是否已存在该用户信息
        $userModel = $this->db_app->table('user')->where('email|mobile|mk_id','=',$postData['account'])->find();
        if (empty($userModel)){
            $this->error('账号不存在');
        }
        if($userModel['status']=='2'){
            $this->error('抱歉！您的账号被限制登录');
        }
        if(!verifyMD5Code($postData['password'],$userModel['password'])){
            $this->error('密码错误');
        }

        $this->success("登录成功", ['userInfo' => $userModel, 'token' => $this->getToken()]);
    }

    /***
     * Action 密码登录
     * @author ywf
     * @license /api/user/pwdLogin POST
     * @para string mobile_prefix 手机国际区号，默认86|Y
     * @para string mobile   手机号|Y
     * @para string password 密码|Y
     * @field string code   1:成功;0:失败
     * @field string msg    信息提示
     * @field string data.userInfo    用户信息
     * @field string data.token    token
     * @jsondata {"mobile":"18312345671","password":"123456","captcha":"123456","sid":"j8qcr3e2cgotaad6sepqbh13j6"}
     * @jsondatainfo {"code":1,"msg":"登录成功","time":"1572510481","data":{"userInfo":{},"token":"sdfsdfsdfsdf"}}
     */
    public function pwdLogin()
    {
        $mobile_prefix = $this->request->post('mobile_prefix') ?: '86';
        $mobile = $this->request->post('mobile');
        $password = $this->request->post('password');
        if (empty($mobile)) {
            $this->error('请输入手机号');
            return false;
        }
        if (empty($password)) {
            $this->error('请输入密码');
            return false;
        }

        //查询用户是否存在
        $user = $this->db_app->table('user')->where(['mobile_prefix' => $mobile_prefix, 'mobile' => $mobile])->find();
        if (!$user) {
            $this->error('账号未找到,请先注册');
            return false;
        }
        if (empty($user['password'])) {
            $this->error('请先设置密码');
            return false;
        }
        //检验密码是否正确
        if (verifyMD5Code($password, $user['password']) != $user['password']) {
            $this->error('密码不正确');
            return false;
        }
        //登录操作
        $this->direct($user['password']);
        if ($this->isLogin()) {
            $this->success("登录成功", ['userInfo' => $user, 'token' => $this->getToken()]);
        } else {
            $this->error('登录失败');
        }
    }

    /***
     * Action 发送短信验证码[同一号码1分钟1条]
     * @author ywf
     * @license /api/user/sendCode POST
     * @para string mobile_prefix 手机国际区号，默认86|Y
     * @para string mobile 手机号|Y
     * @field string code   1:成功;0:失败
     * @field string msg    错误提示
     * @jsondata {"mobile":"18339817892"}
     * @jsondatainfo {"code":1,"msg":"发送成功","time":"1572510481","data":null}
     */
    public function sendCode()
    {
        $mobile = $this->request->post('mobile');
        $mobile_prefix = $this->request->post('mobile_prefix') ?: '86'; //2020-01-04添加国外手机区号
        if (empty($mobile)) {
            $this->error('请填写手机号');
        }
        //判定该1分钟内是否已发送
        $session = Session::get('mobile' . $mobile);
        if ($session) {
            if (time() - $session['time'] < 60) {
                $time = 60 - (time()->$session['time']);
                $this->error('请在' . $time . '秒后再次发送');
                return false;
            }
        }
        $code = mt_rand(100000, 999999);
        Session::set('mobile' . $mobile, ['time' => time(), 'code' => $code]);
        $alisms = new Alisms();
        if (empty($mobile_prefix) || $mobile_prefix == '86') {
            $alisms = $alisms->template('SMS_151771231');
        } else {
            $alisms = $alisms->template('SMS_181860050'); //国际短信模板
            $mobile = $mobile_prefix . $mobile;

        }

        $alisms->param(['code' => $code]);
        $alisms->mobile($mobile);
        $res = $alisms->send();

        if ($res === FALSE) {
            $this->error('发送验证码失败');
            return false;
        }

        $this->success('发送成功');


    }

    /***
     * Action 通过手机号注册
     * @author ywf
     * @license /api/user/register POST
     * @para string mk_id   用户名|Y
     * @para string password1   密码|Y
     * @para string password2   确认密码|Y
     * @para string email   邮箱|Y
     * @para string name    姓名|Y
     * @para string mobile_prefix 手机国际区号，默认86|Y
     * @para string mobile 手机号|Y
     * @para string code   短信验证码|Y
     * @para string company    企业名称|Y
     * @para string position   职位|Y
     * @para string intro      简介|N
     * @para string direction  账号类型，0：用户注册，1：机构注册|Y
     * @field string code   1:成功;0:失败
     * @field string msg    错误提示
     * @jsondata {"name":"ywf","mobile":"18339817892","code":"123456","password":"12345678","company":"阿里巴巴", "position":"经理"}
     * @jsondatainfo {"code":1,"msg":"注册成功","time":"1572510481","data":null}
     */
    public function register()
    {
        $user = new UserModel();
        $postData = $this->request->post();
        if(!$user->isNotReg($postData['mk_id'],'mk_id')) {
            $this->error('该账号已被注册');
        }
        if(!$user->isNotReg($postData['email'],'email')) {
            $this->error('该邮箱已被注册');
        }
        if(!$user->isNotReg($postData['mobile'],'mobile', $postData['mobile_prefix'])) {
            $this->error('该手机号已被注册');
        }
        if ($postData['password1'] != $postData['password2']) {
            $this->error('两次输入的密码不一致');
        }
        $session = Session::get('mobile' . $postData['mobile']);
        if ($postData['code'] != $session['code']) {
            $this->error('验证码错误');
        }

        $postData['password'] = generateMD5WithSalt($postData['password']);
        $postData['status'] = 0;
        $postData['tid'] = $postData['direction'];
        $postData['nickname'] = hideAccount($postData['mobile']);
        UserModel::create($postData,true);
        $this->success('账号注册成功！');

    }


    /**
     * 我的评论
     */
    public function comment()
    {
        $page = $this->request->get('page', 1, 'intval');
        if ($page > 0) {
            $condition = [
                'uid' => $this->user->id,
            ];
            // 获取前8条评论文章ID
            $commentModel = new Comment();
            $archivesIds = $commentModel->getCommentArchiveIds($condition, 8, $page);
            if (empty($archivesIds)) {
                $this->success('', []);
            }
            // 获取文章列表
            $articleModel = new Article();
            $archiveList = $articleModel->getArchivesList(['id' => ['in', $archivesIds]], true, null, []);
            // 获取评论
            $condition['aid'] = ['in', $archivesIds];
            $commentList = $commentModel->getList($condition, true);

            $data = [];
            foreach ($commentList as $item) {
                if (isset($data[$item['aid']])) {
                    array_push($data[$item['aid']], $item);
                } else {
                    $data[$item['aid']] = [$item];
                }
            }

            $list = [];
            foreach ($archiveList as $item) {
                $item['comments'] = $data[$item['id']];
                $list[$item['id']] = $item;
            }
            $data = [];
            foreach ($archivesIds as $id) {
                array_push($data, $list[$id]);
            }

            $this->success('', $data);
        }
    }

    /**
     * 我的收藏
     */
    public function collect()
    {
        $page = $this->request->get('page', 1, 'intval');
        if ($page > 0) {
            $condition = [
                'uid' => $this->user->id,
            ];
            // 获取前8条评论文章ID
            $praiseModel = new Praise();
            $archivesIds = $praiseModel->getCollectArchiveIds($condition, 8, $page);
            if (empty($archivesIds)) {
                $this->success('', []);
            }
            // 获取文章列表
            $articleModel = new Article();
            $archiveList = $articleModel->getArchivesList(['id' => ['in', $archivesIds]], true, null);

            $list = [];
            foreach ($archiveList as $item) {
                $list[$item['id']] = $item;
            }

            $data = [];
            foreach ($archivesIds as $id) {
                if (isset($list[$id])) {
                    array_push($data, $list[$id]);
                }
            }

            $this->success('', $data);
        }
    }

    /**
     * 绑定账号
     */
    public function bind()
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');

        $rule = [
            'password' => 'require|length:3,48',
        ];
        if (\think\Validate::is($username, 'email')) {
            // 邮箱
            $field = 'email';
            $rule['username'] = 'require|email';
        } else {
            // 手机
            $field = 'mobile';
            $rule['username'] = 'require|mobile';
        }

        $msg = [
            'username.require' => '请输入用户名',
            'username.email' => '邮箱地址有误',
            'username.mobile' => '手机格式有误',
            'password.require' => '请输入密码',
            'password.length' => '输入密码有误',
        ];
        $data = [
            'username' => $username,
            'password' => $password,
        ];
        $validate = new \think\Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            $this->error($validate->getError());
            return false;
        }

        $user = \app\api\model\User::get([$field => $username]);
        if (!$user) {
            $this->error('账号未找到,或已绑定小程序');
        }
        $third = OauthThird::where(['uid' => $user->id, 'platform' => 'wxapp'])->find();
        if ($third) {
            $this->error('账号已经绑定其他小程序账号');
        }

        $third = OauthThird::where(['uid' => $this->user->id, 'platform' => 'wxapp'])->find();
        if (!$third) {
            $this->error('未找到登录信息', null, 401);
        }

        $oldUid = $this->user->id;
        if ($this->initLogin($username, $password)) {
            $third->uid = $this->user->id;
            $third->save();
            $user = $this->user;
            if (empty($user->avatar) || empty($user->nickname)) {
                if (empty($user->avatar)) {
                    $user->avatar = $third['avatar'];
                }

                if (empty($user->nickname)) {
                    $user->nickname = $third['nickname'];
                }

                $user->save();
            }
            // 更改点赞、收藏、评论信息
            Db::table('praise')->where('uid', $oldUid)->setField('uid', $this->user->id);
            Db::table('comment')->where('uid', $oldUid)->setField('uid', $this->user->id);

            $this->clearToken($oldUid);
            $this->success("绑定成功", ['userInfo' => $this->user->getInfo(), 'token' => $this->getToken()]);
        } else {
            $this->error('绑定失败');
        }

    }



    /***
     * Action 获取图片验证码
     * @author ywf
     * @license /api/user/getVerify POST
     * @para string 不需要   无|N
     * @field string code   1:成功;0:失败
     * @field string msg    空
     * @field string data.verify_url 二维码图片地址
     * @field string data.sid 唯一标识
     * @jsondata 无
     * @jsondatainfo {"code":1,"msg":"","time":"1572576190","data":{"Verify_url":"http:\/\/morketing.com\/api\/user\/verify?sid=j8qcr3e2cgotaad6sepqbh13j6","sid":"j8qcr3e2cgotaad6sepqbh13j6"}}
     */
    public function getVerify()
    {
        session_start();
        $sid = session_id();#获取单签sessionid
        //获取当前域名
        $request = Request::instance();
        $domain = $request->domain();
        $data['verify_url'] = $domain . '/api/user/verify?sid=' . $sid;
        $data['sid'] = $sid;
        $this->success('', $data);

    }

    //生成验证码
    public function verify()
    {
        $sid = request()->get('sid');
        session_id($sid); #指定sessionid
        session_start();#开启session
        $config = [
            // 验证码字符集合
            'codeSet' => '2345678abcdefghijklmnopqrstuvwxyz',
            // 验证码字体大小(px)
            'fontSize' => 15,
            // 是否画混淆曲线
            'useCurve' => false,
            // 验证码图片高度
            'imageH' => 30,
            // 验证码图片宽度
            'imageW' => 100,
            // 验证码位数
            'length' => 5,
            // 验证成功后是否重置        
            'reset' => true
        ];

        $captcha = new Captcha($config);
        return $captcha->entry($sid); #传入session标识
    }

    /***
     * Action 短信登录
     * @author ywf
     * @license /api/user/messageLogin POST
     * @para string mobile_prefix 手机国际区号，默认86|Y
     * @para string mobile  手机号|Y
     * @para string code    短信验证码|Y
     * @field string code   1:成功;0:失败
     * @field string msg    错误提示
     * @field string data.userInfo    用户信息
     * @field string data.token    token
     * @jsondata {"mobile":"18339123456","captcha":"123456","code":"123456","sid":"j8qcr3e2cgotaad6sepqbh13j6"}
     * @jsondatainfo {"code":1,"msg":"登录成功","time":"1572510481","data":{"userInfo":{},"token":"sdfsdfsdfsdf"}}
     */
    public function messageLogin()
    {
        $mobile_prefix = $this->request->post('mobile_prefix') ?: '86';
        $mobile = $this->request->post('mobile');
        $code = $this->request->post('code');
        if (empty($mobile)) {
            $this->error('请输入手机号');
            return false;
        }
        if (empty($code)) {
            $this->error('请输入短信验证码');
            return false;
        }

        //验证短信验证码
        $session = Session::get('mobile' . $mobile);
        if ($code != $session['code']) {
            $this->error('短信验证码错误');
            return false;
        }
        //查询用户是否存在
        $user = UserModel::get(['mobile_prefix' => $mobile_prefix, 'mobile' => $mobile]);
        if (!$user) {
            $this->error('账号未找到,请先注册');
            return false;
        }

        $this->direct($user->id);
        if ($this->isLogin()) {
            //清除验证码
            Session::delete("mobile" . $mobile);
            $this->success("登录成功", ['userInfo' => $this->user->getInfo(), 'token' => $this->getToken()]);
        } else {
            $this->error('登录失败');
        }
    }




    /***
     * Action 获取手机国家区号列表
     * @author ywf
     * @license /api/user/mobilePrefixList POST
     * @field string code   1:成功;0:失败
     * @field string msg    无
     * @field string data.mobile_prefix_list    国家手机区号列表
     * @jsondata {"mobile":"18312345671","password":"123456","captcha":"123456","sid":"j8qcr3e2cgotaad6sepqbh13j6"}
     * @jsondatainfo {"code":1,"msg":"登录成功","time":"1572510481","data":{"userInfo":{},"token":"sdfsdfsdfsdf"}}
     */
    public function mobilePrefixList()
    {
        $country_mobile_list = Db::name('country_mobile_prefix')->order('id asc')->column('mobile_prefix');
        $this->success('', ['mobile_prefix_list' => $country_mobile_list]);
    }

    /***
     * Action 上传头像
     * @author ywf
     * @license /api/user/uploadAvatar POST
     * @para string file 上传的图片|Y
     * @field string code   1:成功;0:失败
     * @field string msg    无
     * @field string avatar    头像路径
     * @jsondata {"mobile":"18312345671","password":"123456","captcha":"123456","sid":"j8qcr3e2cgotaad6sepqbh13j6"}
     * @jsondatainfo {"code":1,"msg":"登录成功","time":"1572510481","data":{"userInfo":{},"token":"sdfsdfsdfsdf"}}
     */
    public function uploadAvatar()
    {
        $file = request()->file('file');
        $path = ROOT_PATH . 'public' . DS . 'upload/avatar/'. date('Y-m');
        $info = $file->rule("md5")->move($path);
        if ($info) {
            $url = $path.'/'. $info->getSaveName();
            $this->success('', ['avatar' => $url]);
        } else {
            $this->error('上传失败');
        }

    }

    /***
     * Action 账号设置
     * @author ywf
     * @license /api/user/accountSet POST
     * @para string avatar 头像url|N
     * @para string nickname 昵称|Y
     * @para string name 姓名|N
     * @para string mobile 手机号|Y
     * @para string password 密码|Y
     * @para string company 企业名称|N
     * @para string position 职位|N
     * @field string code   1:成功;0:失败
     * @field string msg    无
     * @field string data.mobile_prefix_list    国家手机区号列表
     * @jsondata {"mobile":"18312345671","password":"123456","captcha":"123456","sid":"j8qcr3e2cgotaad6sepqbh13j6"}
     * @jsondatainfo {"code":1,"msg":"登录成功","time":"1572510481","data":{"userInfo":{},"token":"sdfsdfsdfsdf"}}
     */
    public function accountSet()
    {
        $data = $this->request->post();
        if (empty($data['nickname'])) {
            $this->error('昵称不能为空');
        }
        if (empty($data['mobile'])) {
            $this->error('手机号不能为空');
        }
        if (empty($data['password'])) {
            $this->error('密码不能为空');
        }

        $data['password'] = generateMD5WithSalt($data['password']);
        $update = [
            'mobile' => $data['mobile'],
            'avatar' => $data['avatar'],
            'nickname' => $data['nickname'],
            'name' => $data['name'],
            'password' => $data['password'],
            'company' => $data['company'],
            'position' => $data['position'],

        ];
        $res = UserModel::update($update, ['id' => $this->user->id], true);
        if ($res === false) {
            $this->error('保存失败，请重试');
        }
        $this->success('保存成功');

    }




}
