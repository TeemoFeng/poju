<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/14
 * Time: 13:45
 */

namespace app\api\library;

use think\Db;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Loader;
use think\Request;
use think\Response;
use think\Cache;
use think\Config;
use app\api\model\User;

class ApiBase
{
    /**
     * @var Request Request 实例
     */
    protected $request;

    /**
     * @var bool 验证失败是否抛出异常
     */
    protected $failException = false;

    /**
     * @var bool 是否批量验证
     */
    protected $batchValidate = false;

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 用户信息
     *
     * @var null
     */
    protected $user = null;

    /**
     * 会话令牌
     *
     * @var null
     */
    protected $token = null;

    /**
     * 默认响应输出类型,支持json/xml
     * @var string
     */
    protected $responseType = 'json';

    /**
     * 会话时间
     *
     * @var int
     */
    protected $keeptime = 2592000;

    /**
     * 架构方法
     *
     * ApiBase constructor.
     */
    public function __construct(Request $request = null)
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
        header('Access-Control-Allow-Headers:Origin,Content-Type,Accept,Token,X-Requested-With,device');
        $this->request = is_null($request) ? Request::instance() : $request;
        $this->db_app = Db::connect('database_morketing');
        // 控制器初始化
        $this->_initialize();
    }

    /**
     * 初始化操作
     * @access protected
     */
    protected function _initialize()
    {
        // 获取令牌
        $token = $this->request->header('Token');
        // 检测是否需要验证登录
        if (!$this->match($this->noNeedLogin)) {
            //初始化
            $this->initToken($token);
            //检测是否登录
            if (!$this->isLogin()) {
                $this->error('您还没有登录', null, 401);
            }
        } else {
            // 如果有传递token才验证是否登录状态
            if ($token) {
                $this->initToken($token);
            }
        }

    }

    /**
     * 操作成功返回的数据
     * @param string $msg 提示信息
     * @param mixed $data 要返回的数据
     * @param int $code 错误码，默认为1
     * @param string $type 输出类型
     * @param array $header 发送的 Header 信息
     */
    protected function success($msg = '', $data = null, $code = 1, $type = null, array $header = [])
    {
        $this->result($msg, $data, $code, $type, $header);
    }

    /**
     * 操作失败返回的数据
     * @param string $msg 提示信息
     * @param mixed $data 要返回的数据
     * @param int $code 错误码，默认为0
     * @param string $type 输出类型
     * @param array $header 发送的 Header 信息
     */
    protected function error($msg = '', $data = null, $code = 0, $type = null, array $header = [])
    {
        $this->result($msg, $data, $code, $type, $header);
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @access protected
     * @param mixed $msg 提示信息
     * @param mixed $data 要返回的数据
     * @param int $code 错误码，默认为0
     * @param string $type 输出类型，支持json/xml/jsonp
     * @param array $header 发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected function result($msg, $data = null, $code = 0, $type = null, array $header = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => Request::instance()->server('REQUEST_TIME'),
            'data' => $data,
        ];
        // 如果未设置类型则自动判断
        $type = $type ? $type : ($this->request->param(config('var_jsonp_handler')) ? 'jsonp' : $this->responseType);

        if (isset($header['statuscode'])) {
            $code = $header['statuscode'];
            unset($header['statuscode']);
        } else {
            //未设置状态码,根据code值判断
            $code = $code >= 1000 || $code < 200 ? 200 : $code;
        }
        $response = Response::create($result, $type, $code)->header($header);
        throw new HttpResponseException($response);
    }

    /**
     * 设置验证失败后是否抛出异常
     * @access protected
     * @param bool $fail 是否抛出异常
     * @return $this
     */
    protected function validateFailException($fail = true)
    {
        $this->failException = $fail;

        return $this;
    }

    /**
     * 验证数据
     * @access protected
     * @param  array $data 数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array $message 提示信息
     * @param  bool $batch 是否批量验证
     * @param  mixed $callback 回调方法（闭包）
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate($data, $validate, $message = [], $batch = false, $callback = null)
    {
        if (is_array($validate)) {
            $v = Loader::validate();
            $v->rule($validate);
        } else {
            // 支持场景
            if (strpos($validate, '.')) {
                list($validate, $scene) = explode('.', $validate);
            }

            $v = Loader::validate($validate);

            !empty($scene) && $v->scene($scene);
        }

        // 批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }
        // 设置错误信息
        if (is_array($message)) {
            $v->message($message);
        }
        // 使用回调验证
        if ($callback && is_callable($callback)) {
            call_user_func_array($callback, [$v, &$data]);
        }

        if (!$v->check($data)) {
            if ($this->failException) {
                throw new ValidateException($v->getError());
            }

            return $v->getError();
        }

        return true;
    }

    /**
     * 检测当前控制器和方法是否匹配传递的数组
     *
     * @param array $arr 需要验证权限的数组
     * @return boolean
     */
    public function match($arr = [])
    {
        $arr = is_array($arr) ? $arr : explode(',', $arr);
        if (empty($arr)) {
            return false;
        }
        $arr = array_map('strtolower', $arr);
        // 是否存在
        if (in_array(strtolower($this->request->action()), $arr) || in_array('*', $arr)) {
            return true;
        }

        return false;
    }

    /**
     * 根据Token初始化
     *
     * @param string $token Token
     * @return boolean
     */
    public function initToken($token)
    {
        if ($this->user) {
            return true;
        }
        $data = Token::get($token);
        if (!$data) {
            $this->error('会话超时，请重新登录', null, 401);
        }

        $user_id = intval($data['user_id']);
        if ($user_id > 0) {
            $user = User::get($user_id);
            if (!$user) {
                $this->error('网络繁忙', null, 403);
            }
            if ($user['status'] == 2) {
                $this->error('该账户已被锁定');
            }
            $this->user = $user;
        } else {
            $this->error('网络繁忙', null, 403);
        }
    }

    /**
     * 判断是否登录
     */
    public function isLogin()
    {
        return $this->user ? true : false;
    }

    /**
     * 直接登录账号
     *
     * @param $uid
     * @return bool
     */
    public function direct($uid)
    {
        $user = User::get($uid);
        if ($user) {
            if ($user['status'] == 2) {
                $this->error('该账户已被锁定');
            }
            $this->user  = $user;
            $this->token = sys_uuid();
            Token::clear($user->id);
            Token::set($this->token, $user->id, $this->keeptime);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取会话令牌
     *
     * @return null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * 注销
     *
     * @return boolean
     */
    public function logout()
    {
        if (!$this->user) {
            return false;
        }
        //设置登录标识
        $this->user = null;
        //删除Token
        Token::delete($this->token);
        return true;
    }

    /**
     * 用户登录
     *
     * @param string $account 邮箱、手机号
     * @param string $password 密码
     * @return boolean
     */
    public function initLogin($account, $password)
    {
        $field = \think\Validate::is($account, 'email') ? 'email' : 'mobile';
        $user  = User::get([$field => $account]);
        if (!$user) {
            return false;
        }

        if ($user->status == 2) {
            $this->error('绑定账号已被锁定');
            return false;
        }

        if (!verifyMD5Code($password, $user['password'])) {
            $this->error('绑定账号密码错误');
            return false;
        }

        //直接登录会员
        $this->direct($user->id);

        return true;
    }

    /**
     * 删除token
     *
     * @param $uid
     */
    public function clearToken($uid)
    {
        Token::clear($uid);
    }

    /**
     * 获取微信access_token
     */
    public function getAccessToken()
    {
      	$token = Cache::get('wx_app_access_token');
        if ($token) {
          return $token;
        }
        // 重启获取token
        $wechat = Config::get('wechat');
        $params = [
            'grant_type' => 'client_credential',
            'appid'      => $wechat['wxappid'],
            'secret'     => $wechat['wxappsecret'],
        ];

        $result = sendRequest('https://api.weixin.qq.com/cgi-bin/token', $params, 'GET');
 
        if ($result['ret']) {
          $result = json_decode($result['msg'],true);
          if (isset($result['access_token'])) {
            // 缓存token
            Cache::set('wx_app_access_token', $result['access_token'], $result['expires_in'] - 200);
            return $result['access_token'];
          } else {
            return false;
          }
        } else {
          // 异常
          return false;
        }
    }

    public function createNickname($length = 8)
    {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ( $i = 0; $i < $length; $i++ )
        { // www.jbxue.com
            // 这里提供两种字符获取方式
            // 第一种是使用substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组$chars 的任意元素
            // $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $password;

    }

}