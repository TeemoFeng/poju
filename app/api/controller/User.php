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
    protected $noNeedLogin = ['login', 'sendCode', 'register', 'mobileLogin', 'mobilePrefixList'];



    /***
     * Action 密码登录
     * @author ywf
     * @license /api/user/login POST
     * @para string account   用户名/手机号/邮箱|Y
     * @para string password   密码|Y
     * @field string code   1:成功;0:失败
     * @field string msg    code=0:1.账号不存在。2.抱歉！您的账号被限制登录,3.密码错误,code=1:登录成功
     * @field string data.userInfo    用户信息
     * @field string data.token    token值
     * @jsondata {"account":"1234","password":"123456"}
     * @jsondatainfo {"code":1,"msg":"登录成功","time":"1578992818","data":{"userInfo":{"id":2521,"mk_id":"1234","mobile_prefix":"86","mobile":"15011555866","email":"123456@qq.com","nickname":"150****5866","password":"6x12Vc8V79786P65e03]b0ia8s3f_d1u15Na123e*89lde88","avatar":"","tid":0,"intro":"","status":0,"display_order":10000,"create_time":1578916569,"update_time":1578916569,"tags":"","company":"111","position":"dfsd","token":"","name":"1234","is_guest":1},"token":"0fa93b27-5229-4dce-9f52-0c2b070291d1"}}
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
        $this->direct($userModel['id']);
        $this->success("登录成功", ['userInfo' => $userModel, 'token' => $this->getToken()]);
    }

    /***
     * Action 手机国家区号列表
     * @author ywf
     * @license /api/user/mobilePrefixList POST
     * @field string code   1:成功;0:失败
     * @field string msg    无
     * @field string data.mobile_prefix_list    国家手机区号列表
     * @jsondata 无
     * @jsondatainfo {"code":1,"msg":"登录成功","time":"1572510481","data":{"userInfo":{},"token":"sdfsdfsdfsdf"}}
     */
    public function mobilePrefixList()
    {
        $country_mobile_list = $this->db_app->table('country_mobile_prefix')->order('id asc')->column('mobile_prefix');
        $this->success('', ['mobile_prefix_list' => $country_mobile_list]);
    }

    /***
     * Action 发送短信验证码[同一号码1分钟1条]
     * @author ywf
     * @license /api/user/sendCode POST
     * @para string mobile_prefix 手机国际区号，默认86|Y
     * @para string mobile 手机号|Y
     * @field string code   1:成功;0:失败
     * @field string msg    code=0:1.请在20秒后再次发送,2.发送验证码失败。code=1:发送成功
     * @jsondata {"mobile_prefix":"86","mobile":"18339817892"}
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
     * Action 手机号登录
     * @author ywf
     * @license /api/user/mobileLogin POST
     * @para string mobile_prefix 手机国际区号，默认86|Y
     * @para string mobile   手机号|Y
     * @para string code     短信验证码|Y
     * @field string code   1:成功;0:失败
     * @field string msg    code=0:1.短信验证码错误,2.账号未找到,请先注册,3.登录失败。code=1:登录成功
     * @field string data.userInfo    用户信息
     * @field string data.token    token
     * @jsondata {"mobile_prefix":"86","mobile":"18339817822","code":"1234"}
     * @jsondatainfo {"code":1,"msg":"登录成功","time":"1578994275","data":{"userInfo":{"id":2385,"mk_id":"","mobile_prefix":"86","mobile":"18339817892","email":"","nickname":"闫伟峰","password":"dUf8U44Xe1Ycf05bQb9Zfc03aZ09u34rd6*0ck20O50Lc6Za","avatar":"/static/backend/images/avatar.png","tid":0,"intro":"","status":0,"display_order":10000,"create_time":1574561678,"update_time":1574561678,"tags":"","company":"爱造科技","position":"后台","token":"","name":"闫伟峰","is_guest":1},"token":"40d8e2d2-1990-4aa5-a0b3-842dfc718617"}}
     */
    public function mobileLogin()
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
        }
        $session = Session::get('mobile' . $mobile);
        if ($code != $session['code']) {
            $this->error('短信验证码错误');
        }

        //查询用户是否存在
        $user = $this->db_app->table('user')->where(['mobile_prefix' => $mobile_prefix, 'mobile' => $mobile])->find();
        if (!$user) {
            $this->error('账号未找到,请先注册');
            return false;
        }

        //登录操作
        $this->direct($user['id']);
        if ($this->isLogin()) {
            $this->success("登录成功", ['userInfo' => $user, 'token' => $this->getToken()]);
        } else {
            $this->error('登录失败');
        }
    }



    /***
     * Action 注册
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
     * @field string msg    code=0:1.该账号已被注册,2.该邮箱已被注册,3.该手机号已被注册,4.两次输入的密码不一致,5.验证码错误,。code=1:账号注册成功
     * @jsondata {"mk_id":"ywf","password1":"123456","password2":"123456","email":"1234567@qq.com","name":"yanceshi","mobile_prefix":"86","mobile":"18339817899","code":"1234","company":"阿里巴巴", "position":"经理","intro":"sdfsf","direction":"0"}
     * @jsondatainfo {"code":1,"msg":"账号注册成功","time":"1572510481","data":null}
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

        $postData['password'] = generateMD5WithSalt($postData['password1']);
        $postData['status'] = 0;
        $postData['tid'] = $postData['direction'];
        $postData['nickname'] = hideAccount($postData['mobile']);
        unset($postData['password1'],$postData['password2']);
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


}
