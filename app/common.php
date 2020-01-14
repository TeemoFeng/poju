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
    if (empty($url)) {
        return '';
    }

    $host = request()->root(true);

    if ($reverse) {
        return str_replace($host, '', $url);
    } else {
        if (empty($url)) {
            return $host . '/static/api/img/avatar.png';
        }
        if($user_type == 1) {
            //如果原先是嘉宾权限被修改之后，头像地址
            if (strpos($url, 'upload') !== false) {
                $host = \think\Config::get('poju_avatar_url');
                return $url && strpos($url, 'http') !== false ? $url : $host . $url;
            }
            return $url && strpos($url, 'http') !== false ? $url : $host . $url;
        } else {
            $host = \think\Config::get('poju_avatar_url');
            return $url && strpos($url, 'http') !== false ? $url : $host . $url;

        }
    }
}
