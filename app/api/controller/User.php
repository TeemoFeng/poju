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
use app\backstage\model\SysAdmin;
use think\Cache;
use think\Config;
use think\Db;
use think\Exception;
use think\Log;
use think\Request;
use think\Session;
use Tools\Alisms;
use app\api\model\User as UserModel;
use Tools\Email;

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
    protected $noNeedLogin = ['login', 'collectList', 'cancelCollect', 'sendCode', 'register', 'mobileLogin', 'mobilePrefixList', 'updateInfo', 'sendMailCode', 'wxlogin', 'wechat'];



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

                $time = 60 - (time()-$session['time']);
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
     * Action 微信登录请求
     * @author ywf
     * @license /api/user/wxlogin POST
     * @para string 无   无
     * @field string code   1:成功;0:失败
     * @field string data.url   打开微信页面url
     * @jsondata 无
     * @jsondatainfo {"code":1,"msg":"","time":"1572510481","data":{"url":"xxx"}}
     */
    public function wxlogin()
    {
        $state = 'new'.time().mt_rand(10000, 99999);
        $appid =  Config::get('wechat_login')['appid'];
        $redirect_uri = urlencode('https://www.morketing.com/index/o_auth/wechat');
        $url='https://open.weixin.qq.com/connect/qrconnect?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_login&state='.$state.'#wechat_redirect';
        $this->success('', ['url' => $url]);
    }

    //微信登录提交
    public function wechat()
    {
        $code = $this->request->get('code');
        $state = $this->request->get('state');
        if(empty($code)){
            return null;
        }
        $url ='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.Config::get('wechat_login')['appid'].'&secret='.Config::get('wechat_login')['secret'].'&code='.$code.'&grant_type=authorization_code';
        $res = curl_get($url);
        if ($res !== false) {
            $access_token = (array) json_decode($res, true);
            Cache::set('wxlogin_access_token',$access_token['access_token'],7000);
            if (!empty($this->user)) {
                //用户已登录
                $oauth = $this->db_app->table('oauth_third')->where(['unionid' => $access_token['unionid']])->find();
                if (!empty($oauth)) {
                    $this->db_app->table('oauth_third')->where('unionid','=',$oauth['unionid'])->setField('uid',$this->user->id);
                    $this->direct($this->user->id);
                    $this->success('账号绑定成功！');
                }else{
                    $data = $this->getUserInfoByUnionid($access_token);
                    $info['openid'] = $data['openid'];
                    $info['unionid'] = $data['unionid'];
                    $info['nickname'] = $data['nickname'];
                    $info['avatar'] = $data['headimgurl'];
                    $info['platform'] = 'wechat';
                    $info['uid'] = $this->user->id;
                    $this->db_app->table('oauth_third')->insert($info);
                    $this->direct($this->user->id);
                    $this->success('账号绑定成功！');
                }
            } else {
                $oauth = $this->db_app->table('oauth_third')->where(['unionid' => $access_token['unionid']])->find();
                if(empty($oauth))
                {
                    $data = $this->getUserInfoByUnionid($access_token);
                    $info['openid'] = $data['openid'];
                    $info['unionid'] = $data['unionid'];
                    $info['nickname'] = $data['nickname'];
                    $info['avatar'] = $data['headimgurl'];
                    $info['platform'] = 'wechat';
                    $info['uid'] = 0;
                    $this->db_app->table('oauth_third')->insert($info);
                    $this->success('授权成功',['oauth' => $info]);
                }else if($oauth['uid'] == 0){
                    $this->success('授权成功',['oauth' => $oauth]);
                }else{
                    $userModel = UserModel::get(['id' => $oauth['uid']]);
                    $this->direct($userModel['id']);
                    $this->success('登录成功！');
                }
            }
            return json($access_token);
        }else{
            return false;
        }
    }

    public function getUserInfoByUnionid($res)
    {
        $access_token = $res['access_token'];
        $openid = $res['openid'];
        $url='https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid;
        $info = curl_get($url);
        return json_decode($info, true);
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

    /***
     * Action 我的收藏列表
     * @author ywf
     * @license /api/user/collectList POST
     * @para string page  页面数,默认1|Y
     * @para string page_size  一页显示条数,默认10|N
     * @field string code   1:成功;0:失败
     * @field string msg    无提示
     * @field string data.count   收藏总数
     * @field string data.list    收藏列表
     * @field string list.user_id 用户id
     * @field string list.video_id 视频id
     * @field string list.collection_id 收藏id
     * @field string list.title    视频标题
     * @field string list.tag     视频标签
     * @field string list.profile 视频简介
     * @field string list.img     视频封面图
     * @field string list.views   浏览数
     * @field string list.likes   点赞数
     * @field string list.release_user   发布人名字
     * @field string list.avatar   发布人头像
     * @field string list.create_time   创建时间
     * @jsondata {"page":"1"}
     * @jsondatainfo {"code":1,"msg":"","time":"1581156449","data":{"count":1,"list":[{"user_id":2385,"video_id":4,"create_time":"2020-01-17 15:06:23","collection_id":1,"title":"视频报道1","tag":"视频报道","profile":"放松放松的","img":"http:\/\/poju.com\/upload\/image\/2020-01\/d46a8c29b2b33b2ae78c4acb89215834.png","views":11,"likes":1,"release_user":"admin","avatar":"\/static\/api\/img\/avatar.png"}]}}
     */
    public function collectList()
    {
        $page = $this->request->post('page', 1, 'intval');
        $page_size = $this->request->post('page_size', 10, 'intval');
        if (empty($this->user)) {
            $this->error('请先登录');
        }
        $count = $this->db_app->table('video_collection')->where(['uid' => $this->user->id])->count();
        $num = ceil($count/$page_size);
        if ($page > $num) {
            $list = [];
        } else {
            $list = $this->db_app->table('video_collection')->where(['uid' => $this->user->id])->order('create_time', 'desc')->limit(($page - 1)*$page_size, $page_size)->select();
            $host = request()->root(true);
            $videoModel = Db::name('report');
            $admin = new SysAdmin();
            array_walk($list, function(&$v) use ( $videoModel, $host, $admin) {
                $video_info = $videoModel->field('id video_id,title,tag,profile,img,views,likes,release_user,create_time')->where(['id' => $v['video_id']])->find();
                $v['user_id'] = $v['uid'];
                $v['collection_id'] = $v['id'];
                unset($v['id'],$v['uid']);
                $v['video_id'] = $video_info['video_id'];
                $v['title'] = $video_info['title'];
                $v['tag'] = $video_info['tag'];
                $v['profile'] = $video_info['profile'];
                if ($video_info['img'] && strpos($video_info['img'], 'http') === false)
                {
                    $v['img'] =  $host . $video_info['img'];
                }
                $v['views'] = $video_info['views'];
                $v['likes'] = $video_info['likes'];
                $admin_info = $admin->where(['id' => $video_info['release_user']])->find();
                $v['release_user'] = $admin_info['account'];
                $v['avatar'] = isset($admin_info['avatar']) && !empty($admin_info['avatar']) ?: '/static/api/img/avatar.png';
            });

        }

        $this->success('', ['count' => $count, 'list' => $list]);
    }

    /***
     * Action 取消收藏
     * @author ywf
     * @license /api/user/cancelCollect POST
     * @para string user_id   用户id|Y
     * @para string video_id  视频id|Y
     * @field string code   1:成功;0:失败
     * @field string msg    1.取消失败,2.取消成功
     * @jsondata {"collection_id":"1"}
     * @jsondatainfo {"code":1,"msg":"取消成功","time":"1581157326","data":{"collection_count":1}}
     */
    public function cancelCollect()
    {
        $user_id = $this->request->post('user_id');
        $video_id = $this->request->post('video_id');
        if (empty($user_id) || empty($video_id)) {
            $this->error('未找到该收藏');
        }
        $where['uid'] = $user_id;
        $where['video_id'] = $video_id;

        $collection_info = $this->db_app->table('video_collection')->where($where)->find();
        if (empty($collection_info)) {
            $this->error('未找到该收藏');
        }
        $res = $this->db_app->table('video_collection')->where(['id' => $collection_info['id']])->delete();
        if ($res === false) {
            $this->error('取消失败');
        }
        $count = Db::name('report')->where(['id' => $collection_info['video_id']])->setDec('collections', 1);
        $count = Db::name('report')->where(['id' => $collection_info['video_id']])->value('collections');
        $this->success('取消成功', ['collection_count' => $count]);

    }

    /***
     * Action 我的点赞列表
     * @author ywf
     * @license /api/user/likesList POST
     * @para string page  页面数,默认1|Y
     * @para string page_size  一页显示条数,默认10|N
     * @field string code   1:成功;0:失败
     * @field string msg    无提示
     * @field string data.count   点赞总数
     * @field string data.list    点赞列表
     * @field string list.user_id 用户id
     * @field string list.video_id 视频id
     * @field string list.likes_id 点赞id
     * @field string list.title    视频标题
     * @field string list.tag     视频标签
     * @field string list.profile 视频简介
     * @field string list.img     视频封面图
     * @field string list.views   浏览数
     * @field string list.likes   点赞数
     * @field string list.release_user   发布人名字
     * @field string list.avatar   发布人头像
     * @field string list.create_time   创建时间
     * @jsondata {"page":"1"}
     * @jsondatainfo {"code":1,"msg":"","time":"1581156449","data":{"count":1,"list":[{"user_id":2385,"video_id":4,"create_time":"2020-01-17 15:06:23","likes_id":1,"title":"视频报道1","tag":"视频报道","profile":"放松放松的","img":"http:\/\/poju.com\/upload\/image\/2020-01\/d46a8c29b2b33b2ae78c4acb89215834.png","views":11,"likes":1,"release_user":"admin","avatar":"\/static\/api\/img\/avatar.png"}]}}
     */
    public function likesList()
    {
        $page = $this->request->post('page', 1, 'intval');
        $page_size = $this->request->post('page_size', 10, 'intval');
//        if (empty($this->user)) {
//            $this->error('请先登录');
//        }
        $count = $this->db_app->table('video_likes')->where(['uid' => $this->user->id])->count();
        $num = ceil($count/$page_size);
        if ($page > $num) {
            $list = [];
        } else {
            $list = $this->db_app->table('video_likes')->where(['uid' => $this->user->id])->order('create_time', 'desc')->limit(($page - 1)*$page_size, $page_size)->select();
            $host = request()->root(true);
            $videoModel = Db::name('report');
            $admin = new SysAdmin();
            array_walk($list, function(&$v) use ( $videoModel, $host, $admin) {
                $video_info = $videoModel->field('id video_id,title,tag,profile,img,views,likes,release_user,create_time')->where(['id' => $v['video_id']])->find();
                $v['user_id'] = $v['uid'];
                $v['likes_id'] = $v['id'];
                unset($v['id'], $v['uid']);
                $v['video_id'] = $video_info['video_id'];
                $v['title'] = $video_info['title'];
                $v['tag'] = $video_info['tag'];
                $v['profile'] = $video_info['profile'];
                if ($video_info['img'] && strpos($video_info['img'], 'http') === false)
                {
                    $v['img'] =  $host . $video_info['img'];
                }
                $v['views'] = $video_info['views'];
                $v['likes'] = $video_info['likes'];
                $admin_info = $admin->where(['id' => $video_info['release_user']])->find();
                $v['release_user'] = $admin_info['account'];
                $v['avatar'] = isset($admin_info['avatar']) && !empty($admin_info['avatar']) ?: '/static/api/img/avatar.png';
            });

        }

        $this->success('', ['count' => $count, 'list' => $list]);
    }

    /***
     * Action 取消点赞
     * @author ywf
     * @license /api/user/cancelLikes POST
     * @para string user_id   用户id|Y
     * @para string video_id  视频id|Y
     * @field string code   1:成功;0:失败
     * @field string msg    1.取消失败,2.取消成功
     * @jsondata {"collection_id":"1"}
     * @jsondatainfo {"code":1,"msg":"取消成功","time":"1581157326","data":{"likes_count":1}}
     */
    public function cancelLikes()
    {
        $user_id = $this->request->post('user_id');
        $video_id = $this->request->post('video_id');
        if (empty($user_id) || empty($video_id)) {
            $this->error('未找到该点赞');
        }
        $where['uid'] = $user_id;
        $where['video_id'] = $video_id;

        $collection_info = $this->db_app->table('video_likes')->where($where)->find();
        if (empty($collection_info)) {
            $this->error('未找到该点赞');
        }
        $res = $this->db_app->table('video_likes')->where(['id' => $collection_info['id']])->delete();
        if ($res === false) {
            $this->error('取消失败');
        }
        Db::name('report')->where(['id' => $collection_info['video_id']])->setDec('likes', 1);
        $count = Db::name('report')->where(['id' => $collection_info['video_id']])->value('likes');
        $this->success('取消成功', ['likes_count' => $count]);

    }

    /***
     * Action 峰会官网用户设置，更新用户信息
     * @author ywf
     * @license /api/user/updateInfo POST
     * @para string user_id  用户id|Y
     * @para string avatar   用户头像|Y
     * @para string mk_id    用户名|Y
     * @para string mobile  手机号|Y
     * @para string code    手机号验证码|Y
     * @para string email   邮箱号|Y
     * @para string email_code   邮箱验证码|Y
     * @para string oldpwd   原始密码|Y
     * @para string password   新密码|Y
     * @field string code   1:成功;0:失败
     * @field string msg    1.保存成功
     * @jsondata {"nickname":"1234"}
     * @jsondatainfo {"code":1,"msg":"保存成功","time":"1578992818","data":{"userInfo":{"id":2521,"mk_id":"1234","mobile_prefix":"86","mobile":"15011555866","email":"123456@qq.com","nickname":"150****5866","password":"6x12Vc8V79786P65e03]b0ia8s3f_d1u15Na123e*89lde88","avatar":"","tid":0,"intro":"","status":0,"display_order":10000,"create_time":1578916569,"update_time":1578916569,"tags":"","company":"111","position":"dfsd","token":"","name":"1234","is_guest":1}}}
     */
    public function updateInfo()
    {
        $postData = $this->request->post();
        if (empty($postData['user_id'])) {
            $this->error('请先登录');
        }
        if (isset($postData['avatar'])) {
            $update['avatar'] = $postData['avatar'];
        }
        if (isset($postData['mk_id'])) {
            $update['mk_id'] = $postData['mk_id'];
        }
        $user_info = $this->db_app->table('user')->where(['id' => $postData['user_id']])->find();
        if (isset($postData['mobile'])) {
            $session = Session::get('mobile' . $postData['mobile']);
            if ($postData['code'] != $session['code']) {
                $this->error('短信验证码错误');
            }
            $update['mobile'] = $postData['mobile'];

        }
        if(isset($postData['email'])){
            if($postData['email_code'] != session('emailCode' . $postData['email'])){
                $this->error('邮件验证码输入错误');
            }
            $update['email'] = $postData['email'];

        }
        if(isset($postData['password'])){
            if(!verifyMD5Code($postData['oldpwd'],$user_info['password'])){
                $this->error('原始密码输入错误');
            }
            $postData['password'] = generateMD5WithSalt($postData['password']);
            $update['password'] = $postData['password'];
        }
        $this->db_app->table('user')->where(['id' => $user_info['id']])->update($update);
        $new_user_info = $this->db_app->table('user')->where(['id' => $user_info['id']])->find();
        $this->success('保存成功', ['userInfo' => $new_user_info]);
    }

    /***
     * Action 发送邮箱验证码
     * @author ywf
     * @license /api/user/sendMailCode POST
     * @para string email  邮箱号|Y
     * @field string code   1:成功;0:失败
     * @field string msg    1.邮件已发送,0.邮件发送失败
     * @jsondata {"email":"1111@qq.com"}
     * @jsondatainfo {"code":1,"msg":"邮件已发送","time":"1581157326","data":null}
     */
    public function sendMailCode()
    {
        $email = $this->request->post('email');
        $code = mt_rand(100000,999999);
        session('emailCode' . $email, $code);
        try {
            $mail = new Email();
            $mail->subject('morketing 邮箱验证码');
            $mail->message('尊敬的morketing用户您好：<br/>您于 '.date('Y-m-d H:i:s',time()).' 发起更换邮箱的请求，本次验证码为:'.$code.' 若非本人操作请忽略。', true);
            $mail->to($email, 'morketing用户');
            $result = $mail->send();

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        if ($result) {
            $this->success('邮件已发送');
        } else  {
            $this->success('邮件发送失败');
        }


    }



}
