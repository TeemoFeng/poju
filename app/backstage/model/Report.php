<?php
/**
 * Created by PhpStorm.
 * User: Uroaming
 * Date: 2020/1/10
 * Time: 19:41
 */
namespace app\backstage\model;

class Report extends BaseModel
{
    public static $types = [
        self::TYPE1 => '图文报道',
        self::TYPE2 => '视频报道',

    ];

    const STATUS1 = 1;
    const STATUS2 = 2;
    const TYPE1 = 1;
    const TYPE2 = 2;

    public $show_fields = 'id report_id,title,tag,profile,img,jump_url';
    public static $status = [
        self::STATUS1 => '下线',
        self::STATUS2 => '上线',
    ];

    public function homepageReportList()
    {
        return $this->where(['status' => self::STATUS2, 'type' => self::TYPE1])->field($this->show_fields)->order('sort ASC')->limit(4)->select()->toArray();
    }

    public function homepageReportVideoList()
    {
        return $this->where(['status' => self::STATUS2, 'type' => self::TYPE2])->field($this->show_fields)->order('sort ASC')->limit(4)->select()->toArray();
    }

}