<?php
/**
 * Created by PhpStorm.
 * User: Uroaming
 * Date: 2020/1/10
 * Time: 10:12
 */
namespace app\backstage\model;

class SummitBanner extends BaseModel
{

    const STATUS1 = 1;
    const STATUS2 = 2;
    protected $resultSetType = 'collection';
    public static $table_field = ['title' => '标题', 'tag' => '标签', 'jump_url' => '跳转地址', 'views' => '点击量'];
    public $show_fields = 'id banner_id,title,tag,img,jump_url';
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

    public function getNameAttr($name)
    {
        return htmlspecialchars_decode($name);
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

    public function homepageBannerList()
    {
        return $this->where(['status' => self::STATUS2])->field($this->show_fields)->order('sort ASC')->limit(5)->select()->toArray();
    }

}