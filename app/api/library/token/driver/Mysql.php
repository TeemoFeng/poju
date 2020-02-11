<?php

namespace app\api\library\token\driver;

use app\api\library\token\Driver;

/**
 * Token操作类
 */
class Mysql extends Driver
{

    /**
     * 默认配置
     * @var array
     */
    protected $options = [
        'table'      => 'user_token',
        'expire'     => 2592000,
        'connection' => [
            // 数据库类型
            'type'        => 'mysql',
            'hostport'    => '3306',
            // 服务器地址
            'hostname'    => '127.0.0.1',
            // 数据库名
            'database'    => 'morketing',
            // 数据库用户名
//            'username'    => 'morketing',
            'username'    => 'root',
            // 数据库密码
//            'password'    => 'aYMy4SC4bxwtxD6X',
            'password'    => 'root',
            // 数据库编码默认采用utf8
            'charset'     => 'utf8',
            // 数据库表前缀
            'prefix'      => '',
            // 数据库调试模式
            'debug'       => false,
        ],
    ];


    /**
     * 构造函数
     * @param array $options 参数
     * @access public
     */
    public function __construct($options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
    }

    /**
     * 获取处理对象
     *
     * @return \think\db\Query
     * @throws \think\Exception
     */
    public function getHandler()
    {
        if ($this->options['connection']) {
            $query = \think\Db::connect($this->options['connection'])->table($this->options['table']);
        } else {
            $query = \think\Db::table($this->options['table']);
        }
        return $query;
    }

    /**
     * 存储Token
     * @param   string $token Token
     * @param   int $user_id 会员ID
     * @param   int $expire 过期时长,0表示无限,单位秒
     * @return bool
     */
    public function set($token, $user_id, $expire = null)
    {
        $expiretime = !is_null($expire) && $expire !== 0 ? time() + $expire : 0;
        $token      = $this->getEncryptedToken($token);
        $this->getHandler()->insert(['token' => $token, 'user_id' => $user_id, 'createtime' => time(), 'expiretime' => $expiretime]);
        return true;
    }

    /**
     * 获取Token内的信息
     * @param   string $token
     * @return  array
     */
    public function get($token)
    {
        $data = $this->getHandler()->where('token', $this->getEncryptedToken($token))->find();
        if ($data) {
            if (!$data['expiretime'] || $data['expiretime'] > time()) {
                //返回未加密的token给客户端使用
                $data['token'] = $token;
                //返回剩余有效时间
                $data['expires_in'] = $this->getExpiredIn($data['expiretime']);
                return $data;
            } else {
                $this->delete($token);
            }
        }
        return [];
    }

    /**
     * 判断Token是否可用
     * @param   string $token Token
     * @param   int $user_id 会员ID
     * @return  boolean
     */
    public function check($token, $user_id)
    {
        $data = $this->get($token);
        return $data && $data['user_id'] == $user_id ? true : false;
    }

    /**
     * 删除Token
     * @param   string $token
     * @return  boolean
     */
    public function delete($token)
    {
        $this->getHandler()->where('token', $this->getEncryptedToken($token))->delete();
        return true;
    }

    /**
     * 删除指定用户的所有Token
     * @param   int $user_id
     * @return  boolean
     */
    public function clear($user_id)
    {
        $this->getHandler()->where('user_id', $user_id)->delete();
        return true;
    }

}