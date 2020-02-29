<?php
/**
 * Created by PhpStorm.
 * User: Uroaming
 * Date: 2019/10/29
 * Time: 16:57
 */

namespace app\backstage\controller;
use app\common\controller\Base;
use think\Session;


class Qrcode extends Base
{
    public function getWxAccessToken(){
        $appid = 'wx9d1698714bf7bcb6';
        $appsecret = '2a86317b3648a76c15c2a6e373759671';
        if (Session::get('access_token_'.$appid) && Session::get('expire_time_'.$appid) > time()){
            return Session::get('access_token_'.$appid);
        } else {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
            $access_token = $this->makeRequest($url);
            $access_token = json_decode($access_token['result'],true);
            Session::set('access_token_'.$appid,$access_token);
            Session::set('expire_time_'.$appid,time()+7000);
            return $access_token;
        }
    }
    /**
     * 发起http请求
     * @param string $url 访问路径
     * @param array $params 参数，该数组多于1个，表示为POST
     * @param int $expire 请求超时时间
     * @param array $extend 请求伪造包头参数
     * @param string $hostIp HOST的地址
     * @return array    返回的为一个请求状态，一个内容
     */
    public function makeRequest($url, $params = array(), $expire = 0, $extend = array())
    {
        if (empty($url)) {
            return array('code' => '100');
        }

        $_curl = curl_init();
        $_header = array(
            'Accept-Language: zh-CN',
            'Connection: Keep-Alive',
            'Cache-Control: no-cache'
        );

        // 只要第二个参数传了值之后，就是POST的
        if (!empty($params)) {
            curl_setopt($_curl, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($_curl, CURLOPT_POST, true);
        }

        if (substr($url, 0, 8) == 'https://') {
            curl_setopt($_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($_curl, CURLOPT_URL, $url);
        curl_setopt($_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($_curl, CURLOPT_USERAGENT, 'API PHP CURL');
        curl_setopt($_curl, CURLOPT_HTTPHEADER, $_header);

        if ($expire > 0) {
            curl_setopt($_curl, CURLOPT_TIMEOUT, $expire); // 处理超时时间
            curl_setopt($_curl, CURLOPT_CONNECTTIMEOUT, $expire); // 建立连接超时时间
        }

        // 额外的配置
        if (!empty($extend)) {
            curl_setopt_array($_curl, $extend);
        }

        $result['result'] = curl_exec($_curl);
        $result['code'] = curl_getinfo($_curl, CURLINFO_HTTP_CODE);
        $result['info'] = curl_getinfo($_curl);
        if ($result['result'] === false) {
            $result['result'] = curl_error($_curl);
            $result['code'] = -curl_errno($_curl);
        }

        curl_close($_curl);
        return $result;
    }


    //获得二维码 type 生成二维码类型
    public function create_qrcode($qrcode_id = '1', $type = 1){

        $ACCESS_TOKEN = $this->getWxAccessToken();
        $qr_path = ROOT_PATH . "/public/upload/qrcode/";
        if (!file_exists($qr_path)) {
            mkdir($qr_path, 0755,true);//判断保存目录是否存在，不存在自动生成文件目录
        }
        $filename = time() . '_' . $qrcode_id .'.png';
        $file_path = $qr_path.$filename;
        if ($type == 1) {
            $url = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token='.$ACCESS_TOKEN['access_token'];
            $qrcode = array(
                'path'			=> 'page/conversation/index?id=' . $qrcode_id,//二维码跳转路径（要已发布小程序）
                'width'			=> 430,

            );
        } else if($type == 2) {
            $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$ACCESS_TOKEN['access_token'];
            $qrcode = array(
                'scene'			=> 'id=' . $qrcode_id,//二维码所带参数
                'width'			=> 430,
                'page'			=> 'page/conversation/index',//二维码跳转路径（要已发布小程序）
                'auto_color'	=> true
            );
        } else {
            return ['code' => 0, 'msg' => '类型错误'];
        }


        $result = $this->sendCmd($url,json_encode($qrcode));//请求微信接口

        $errcode = json_decode($result,true)['errcode'];
        $errmsg = json_decode($result,true)['errmsg'];
        if ($errcode) {
            return ['code' => 0, 'msg' => $errcode . $errmsg];
        }
        $file = fopen($file_path,"w");
        fwrite($file,$result);
        fclose($file);
        return ['code' => 1, 'path' => '/upload/qrcode/'.$filename];
    }

    //开启curl post请求
    function sendCmd($url,$data)
    {
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检测
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:')); //解决数据包大不能提交
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回

        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);
        }
        curl_close($curl); // 关键CURL会话
        return $tmpInfo; // 返回数据
    }

}