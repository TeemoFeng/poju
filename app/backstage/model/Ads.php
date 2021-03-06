<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-07-22
 * Time: 16:47
 */

namespace app\backstage\model;

class Ads extends BaseModel
{
    public function category()
    {
        return $this->belongsTo("Category",'tid');
    }

    //logo地址
    public function getImgAttr($url)
    {
        $host = request()->root(true);
        return $url && strpos($url, 'http') !== false ? $url : $host . $url;

    }
}