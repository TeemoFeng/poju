<?php

namespace Tools;

use think\Cache;

/**
 * 微信分享类
 * 功能特性： 微信公众号分享
 */
class WxShare
{

    /**
     * @var object 对象实例
     */
    protected static $instance;

    // 微信appId
    protected $appId = 'wxdc8ea001341485b7';
    // 微信 appsecret
    protected $appSecret = '82c75c3bb308dbe4302b5a3b17cf88ba';

    /**
     * 类架构函数
     * Auth constructor.
     */
    public function __construct($options = null)
    {
        if ($options) {
            $this->appId = isset($options['appId']) ? $options['appId'] : $this->appId;
            $this->appSecret = isset($options['appSecret']) ? $options['appSecret'] : $this->appSecret;
        }
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return Auth
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance))
        {
            self::$instance = new static($options);
        }

        return self::$instance;
    }
    /**
     * 获取分享参数
     * @param $url
     * @return array
     */
    function getShareParams($url)
    {
        $nonceStr = $this->getNonceStr();
        $jsApiTicket = $this->getJsApiTicket();
        $data = $this->getSignature($url,$jsApiTicket,$nonceStr);
        $data['appId'] = $this->appId;
        $data['nonceStr'] = $nonceStr;
        return $data;
    }

    /**
     * 获取签名
     * @param $url
     * @param $jsApiTicket
     * @param $nonceStr
     * @return array
     */
    function getSignature($url,$jsApiTicket,$nonceStr)
    {
        $arr = [
            'noncestr' => $nonceStr,
            'timestamp' => time(),
            'jsapi_ticket'  => $jsApiTicket,
            'url'   => $url
        ];
        ksort($arr, SORT_STRING);
        $str = urldecode(http_build_query($arr));
        $signature = sha1($str);

        return ['signature'=> $signature,'timestamp'=>$arr['timestamp']];
    }

    /**
     * 获取微信 access_token
     * @return mixed
     */
    function getAccessToken() {
        $accessToken = Cache::get('wx_access_token',false);

        if ($accessToken === false) {
            $tokenUrl="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=". $this->appId ."&secret=" . $this->appSecret;
            $result = curl_get($tokenUrl);
            $result = $result ? json_decode($result,true) : '';
            if ($result && !empty($result['access_token'])) {
                $accessToken = $result['access_token'];
               // Cache::set('wx_access_token',$accessToken,time() + 7000);
               Cache::set('wx_access_token',$accessToken,7000);
            } else {
                abort('500','获取微信 access_token 失败');
            }
        }

        return $accessToken;
    }

    /**
     * 获取微信 jsapi_ticket
     * @return mixed
     */
    function getJsApiTicket()
    {
        $jsApiTicket = Cache::get('wx_jsapi_ticket',false);
        if ($jsApiTicket === false) {
            $accessToken = $this->getAccessToken();
            $jsApiTicketUrl="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $accessToken . "&type=jsapi";
            $result = curl_get($jsApiTicketUrl);
            $result = $result ? json_decode($result,true) : '';
            if ($result && $result['ticket']) {
                $jsApiTicket = $result['ticket'];
                //Cache::set('wx_jsapi_ticket',$jsApiTicket,time() + 7000);
                Cache::set('wx_jsapi_ticket',$jsApiTicket,7000);
            } else {
                abort('500','获取微信 jsapi_ticket 失败');
            }
        }

        return $jsApiTicket;
    }

    /**
     * 获取随机字符串
     * @return string
     */
    function getNonceStr()
    {
        $codeSet = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codeArr = [];
        for ($i = 0; $i < 16; $i++) {
            $codeArr[$i] = $codeSet[mt_rand(0,strlen($codeSet) -1)];
        }
        return implode($codeArr);
    }

}
