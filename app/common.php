<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * curl get 请求
 * @param $url
 * @return mixed
 */
function generateMD5WithSalt($code)
{
    $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ*_+[]';
    $salt = '';
    for ($i = 0;$i<16;$i++)
    {
        $salt.=$str[mt_rand(0,strlen($str)-1)];
    }
    $encrypt_code = md5($code.$salt);
    $char = [];
    for($i = 0;$i < 48;$i+=3)
    {
        $char[$i]=$encrypt_code[$i/3*2];
        $char[$i+1]=$salt[$i/3];
        $char[$i+2] = $encrypt_code[$i / 3 * 2 + 1];
    }
    return implode($char);
}
function verifyMD5Code($code,$ciphertext)
{
    $strMD5Code =[];
    $salt = [];
    for ($i = 0; $i < 48; $i += 3)
    {
        $strMD5Code[$i / 3 * 2] = $ciphertext[$i];
        $strMD5Code[$i / 3 * 2 + 1] = $ciphertext[$i + 2];
        $salt[$i / 3] = $ciphertext[$i + 1];
    }
    return strcmp(md5($code.implode($salt)),implode($strMD5Code))==0?true:false;
}
function curl_get($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $SSL = substr($url, 0, 8) == "https://" ? true : false;
    if ($SSL) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名
    }
    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
}
function getNavDownList($tid,$con)
{
    $t = new \app\backstage\model\Category();
    $items = $t->getChildByPid($tid);
    $nav='';
    foreach ($items as $item){
        $nav.='<dd><a href="'.url('index/'.$con.'/items',['id'=>$item['id']]).'">'.$item['name'].'</a></dd>';
    }
    return $nav;
}
/**
 * 首页Get文章
 * @param $tid
 */
function getArticleByTid($tid)
{
    $art = new \app\backstage\model\Article();
    return $art->withCount('praiseLike')->field('id,name,img,description,views,release_time,uid')->with('userinfo')->where('tid','=',$tid)->order('release_time','desc')->limit(10)->select();
}

function getFlashByTid($tid)
{
    $article = new \app\backstage\model\Article();
    $items = $article->where('tid','=',$tid)->order('release_time','desc')->paginate(10);
    $list = [];
    foreach ($items->items() as $item) {
        if (!isset($list[$item['gdate']])) {
            $list[$item['gdate']] = [];
        }
        array_push($list[$item['gdate']],$item->toArray());
    }
    return $list;
}

function getPartnerList()
{
    $ads = new \app\backstage\model\Ads();
    return $ads->where('tid','=',1)->order('displayorder','asc')->select();
}

function getFineArticle()
{
    $article = new \app\backstage\model\Article();
    $category = new \app\backstage\model\Category();
    $time = strtotime('-1 week');
    return $article->where('tid','in',$category->getChildIdlist(1))->where('release_time','>',$time)->order('views','desc')->limit(5)->field('id,name,img,release_time,views')->select();
}

function getHotLibrary()
{
    $library = new \app\backstage\model\Agenda();
    return $library->order('views','desc')->field('id,name,views')->limit(16)->select();
}

function getAds($tid,$num = 1)
{
    $ads = new \app\backstage\model\Ads();
    return   $ads->where('tid','=',$tid)->order('displayorder','asc')->limit($num)->select();
}

function time_ago($agoTime)
{
    $time = time() - $agoTime;
    if (7776000 >= $time && $time >= 2592000) {
        $num = (int)($time / 2592000);
        return $num.'月前';
    }
    if (2592000 > $time && $time >= 86400) {
        $num = (int)($time / 86400);
        if ($num >= 7){
            $num = intval($num / 7);
            return $num.'周前';
        }
        return $num.'天前';
    }
    if (86400 > $time && $time>= 3600) {
        $num = (int)($time / 3600);
        return $num.'小时前';
    }
    if ($time < 3600 && $time >= 60) {
        $num = (int)($time / 60);
        return $num.'分钟前';
    }
    if($time < 60)
    {
        return '刚刚';
    }
    return date('Y-m-d H:i',$agoTime);
}

function hideAccount($str) {
    if (strpos($str, '@')) {
        $email_array = explode("@", $str);
        $prevfix = (strlen($email_array[0]) < 4) ? "" : substr($str, 0, 3);
        $count = 0;
        $str = preg_replace('/([\d\w+_-]{0,100})@/', '***@', $str, -1, $count);
        $rs = $prevfix . $str;
    } else {
        $pattern = '/(1[345678]{1}[0-9])[0-9]{4}([0-9]{4})/i';
        if (preg_match($pattern, $str)) {
            $rs = preg_replace($pattern, '$1****$2', $str);
        } else {
            $rs = substr($str, 0, 3) . "***" . substr($str, -1);
        }
    }
    return $rs;
}

/**
 * 获取全球唯一标识
 *
 * @return string
 */
function sys_uuid()
{
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff),
        mt_rand(0, 0xffff), mt_rand(0, 0xffff));
}

/**
 * 获取带域名的资源地址
 *
 * @param $url  资源地址
 * @param bool $reverse 去除域名部分
 * @return mixed|string
 */
function sys_repaire_url($url, $user_type = 1, $reverse = false)
{
    $host = request()->root(true);
    if ($reverse) {
        return str_replace($host, '', $url);
    } else {
        if (empty($url)) {
            $host = \think\Config::get('morketing_avatar_url');
            return $host . '/static/api/img/avatar.png';
        }
        if($user_type == 1) {
            //如果原先是嘉宾权限被修改之后，头像地址
//            if (strpos($url, 'upload') !== false) {
//                $host = \think\Config::get('morketing_avatar_url');
//                return $url && strpos($url, 'http') !== false ? $url : $host . $url;
//            }
            $host = \think\Config::get('morketing_avatar_url');
            return $url && strpos($url, 'http') !== false ? $url : $host . $url;
        } else {
            return $url && strpos($url, 'http') !== false ? $url : $host . $url;

        }
    }
}

//判断电脑还是手机访问
function is_mobile(){
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    {
        return true;
    }
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
    {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
            return true;
        }
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT']))
    {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        }
    }
    return false;
}

/*
*功能：php完美实现下载远程图片保存到本地
*参数：文件url,保存文件目录,保存文件名称，使用的下载方式
*当保存文件名称为空时则使用远程文件原来的名称
*/
function getImage($url, $save_dir = '', $filename = '', $type = 0)
{
    if (trim($url) == '') {
        return array('file_name' => '', 'save_path' => '', 'error' => 1);
    }
    if (trim($save_dir) == '') {
        $save_dir = './';
    }
    if (trim($filename) == '') {//保存文件名
        $ext = strrchr($url, '.');
        if ($ext != '.gif' && $ext != '.jpg') {
            return array('file_name' => '', 'save_path' => '', 'error' => 3);
        }
        $filename = time() . $ext;
    }
    if (0 !== strrpos($save_dir, '/')) {
        $save_dir .= '/';
    }
    //创建保存目录
    if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
        return array('file_name' => '', 'save_path' => '', 'error' => 5);
    }
    //获取远程文件所采用的方法
    if ($type) {
        $ch      = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $img = curl_exec($ch);
        curl_close($ch);
    } else {
        ob_start();
        readfile($url);
        $img = ob_get_contents();
        ob_end_clean();
    }
    //$size=strlen($img);
    //文件大小
    $fp2 = @fopen($save_dir . $filename, 'a');
    fwrite($fp2, $img);
    fclose($fp2);
    unset($img, $url);
    return array('file_name' => $filename, 'save_path' => $save_dir . $filename, 'error' => 0);
}