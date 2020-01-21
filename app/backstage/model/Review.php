<?php
/**
 * Created by PhpStorm.
 * User: Uroaming
 * Date: 2020/1/10
 * Time: 19:27
 */
namespace app\backstage\model;

class Review extends BaseModel
{
    const STATUS1 = 1;
    const STATUS2 = 2;
    protected $resultSetType = 'collection';
    public $show_fields = 'id review_id,title,tag,profile,img,jump_url';
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

    public function homepageReviewList()
    {
        return $this->where(['status' => self::STATUS2])->field($this->show_fields)->order('sort ASC')->limit(4)->select()->toArray();
    }
}