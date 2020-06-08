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

    public static $table_field = ['title' => '标题', 'tag' => '标签', 'start_time' => '开始时间', 'end_time' => '结束时间','jump_url' => '跳转地址', 'views' => '点击量'];
//    public static $list = [
//        [
//            'id' => '1',
//            'name' => 'Morketing官方活动',
//        ],
//        [
//            'id' => '2',
//            'name' => '行业活动',
//        ],
//    ];

    public $show_fields = 'id recommend_id,title,tag,start_time,end_time,address,type,is_show,img,jump_url,views';
    protected $resultSetType = 'collection';
    public static $types = [
        self::TYPE1 => '普通轮播广告位',
        self::TYPE2 => '固定强推广告位',
    ];
//    public static $tags = [
//        self::TYPE1 => 'Morketing官方活动',
//        self::TYPE2 => '行业活动',
//    ];

    public static $status = [
        self::STATUS1 => '下线',
        self::STATUS2 => '上线',
    ];

    public function getTitleAttr($title)
    {
        return htmlspecialchars_decode($title);
    }

    public function getTagAttr($k)
    {
        $recommendR = new RecommendRule();
        $tag = $recommendR->get($k);
        return $tag['name'];
    }


    //图片地址
    public function getImgAttr($url)
    {
        $host = request()->root(true);
        return $url && strpos($url, 'http') !== false ? $url : $host . $url;

    }

    //修改时间格式
    public function getStartTimeAttr($start)
    {
        return str_replace('-', '.', $start);
    }

    public function getEndTimeAttr($end)
    {
        return str_replace('-', '.', $end);
    }


    //活动推荐固定广告位
    public function recommendFixed()
    {
        $list =  $this->where(['status' => self::STATUS2, 'type' => self::TYPE2])->field($this->show_fields)->order('sort ASC')->limit(3)->select()->toArray();
        return $list;

    }


    //活动推荐普通广告位
    public function recommendOrdinary ()
    {
        return $this->where(['status' => self::STATUS2, 'type' => self::TYPE1])->field($this->show_fields)->order('sort ASC')->limit(12)->select()->toArray();
    }

}