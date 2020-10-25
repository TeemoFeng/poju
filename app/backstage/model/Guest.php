<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-08-26
 * Time: 16:50
 */
namespace app\backstage\model;
class Guest extends BaseModel
{
    public function category()
    {
        return $this->belongsTo("Category",'cid');
    }

    //logo地址
    public function getAvatarAttr($url)
    {
        $host = request()->root(true);
        return $url && strpos($url, 'http') !== false ? $url : $host . $url;

    }
}