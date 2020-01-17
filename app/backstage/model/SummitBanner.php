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

    public $show_fields = 'id banner_id,title,tag,img,jump_url';
    public static $status = [
        self::STATUS1 => '下线',
        self::STATUS2 => '上线',
    ];

    public function homepageBannerList()
    {
        return $this->where(['status' => self::STATUS2])->field($this->show_fields)->order('sort ASC')->limit(4)->select()->toArray();
    }

}