<?php
/**
 * Created by PhpStorm.
 * User: Uroaming
 * Date: 2020/1/10
 * Time: 17:43
 */

namespace app\backstage\model;

class Recommend extends BaseModel
{
    const TYPE1 = 1;
    const TYPE2 = 2;
    const STATUS1 = 1;
    const STATUS2 = 2;

    public $show_fields = 'id recommend_id,title,tag,start_time,end_time,address,type,img,jump_url,views';
    protected $resultSetType = 'collection';
    public static $types = [
        self::TYPE1 => '固定强推广告位',
        self::TYPE2 => '普通轮播广告位',
    ];

    public static $status = [
        self::STATUS1 => '下线',
        self::STATUS2 => '上线',
    ];

    //图片地址
    public function getImgAttr($url)
    {
        $host = request()->root(true);
        return $url && strpos($url, 'http') !== false ? $url : $host . $url;

    }

    //活动推荐固定广告位
    public function recommendFixed()
    {
        return $this->where(['status' => self::STATUS2, 'type' => self::TYPE2])->field($this->show_fields)->order('sort ASC')->limit(4)->select()->toArray();

    }


    //活动推荐普通广告位
    public function recommendOrdinary ()
    {
        return $this->where(['status' => self::STATUS2, 'type' => self::TYPE1])->field($this->show_fields)->order('sort ASC')->limit(10)->select()->toArray();
    }

}