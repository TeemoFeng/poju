<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/14
 * Time: 14:46
 */

namespace app\api\model;

use think\Db;
use think\Model;
/**
 * 用户
 * Class User
 * @package app\api\model
 */
class User extends Model
{
    /**
     * 表名
     * @var string
     */
    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->db_app = Db::connect('database_morketing');
    }

    protected $connection = [
        // 数据库类型
        'type'        => 'mysql',
        // 服务器地址
        'hostname'    => '127.0.0.1',
        'hostport'    => '3306',
        // 数据库名
        'database'    => 'morketing',
        // 数据库用户名
//        'username'    => 'morketing',
        'username'    => 'root',
        // 数据库密码
//        'password'    => 'aYMy4SC4bxwtxD6X',
        'password'    => 'root',
        // 数据库编码默认采用utf8
        'charset'     => 'utf8',
        // 数据库表前缀
        'prefix'      => '',
        // 数据库调试模式
        'debug'       => false,

    ];

    protected $autoWriteTimestamp = 'int';

    /**
     * 默认分页条数
     *
     * @var int
     */
    protected $defaultLimit = 10;



    /**
     * 获取默认分页数量
     */
    public function getDefaultLimit()
    {
        return $this->defaultLimit;
    }


    /**
     * 基础查询
     * @param $query
     */
    public function base($query)
    {
        $query->where('status', '<>', 2);
    }

    /**
     * 头像
     */
    public function getAvatarAttr($value, $data)
    {
        $is_guest = isset($data['is_guest']) ? $data['is_guest'] : 1;
        return sys_repaire_url($value, $is_guest);
    }

    /**
     * 判断是否绑定账户
     *
     * @return bool|void
     */
    public function getIsbindAttr($value, $data)
    {
        return $data['mobile'] || $data['email'] ? true : false;
    }

    /**
     * 获取展示的字段
     *
     * @return arraygetInfo
     */
    public function getInfo()
    {
        $data = [];
        foreach (['id', 'tid', 'mk_id', 'mobile_prefix','mobile', 'email','nickname', 'avatar', 'isbind', 'name', 'is_guest', 'password', 'company', 'position'] as $field) {

            $data[$field] = $this[$field];
        }

        //查询该用户是否绑定微信
        $oauth = $this->db_app->table('oauth_third')->where(['uid' => $this['id'], 'platform' => 'wechat'])->find();
        $data['wx_bind'] = '0';
        $data['wx_nickname'] = '';
        if (!empty($oauth)) {
            $data['wx_bind'] = '1';
            $data['wx_nickname'] = $oauth['nickname'];
        }
        return $data;
    }

    /**
     * 获取用户列表
     *
     * @param $condition
     * @param $limit
     * @param $page
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getUserList($condition, $limit, $page)
    {
        if (is_null($limit)) {
            $limit = $this->getDefaultLimit();
        }

        $query = $this
            ->field($this->getQueryField())
            ->where($condition)
            ->order('id', 'desc');

        if (is_numeric($limit)) {
            $query->limit($limit)->page($page);
        }

        $data = $query->select();

        return $data;
    }


    public function isNotReg($account,$field, $mobile_prefix = '')
    {
        $where[$field] = $account;
        if (!empty($mobile_prefix)) {
            $where['mobile_prefix'] = $mobile_prefix;
        }
        $userModel = $this->where($where)->find();
        if (empty($userModel)) {
            return true;
        }else{
            return false;
        }
    }
}