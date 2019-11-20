<?php
/**
 * Created by PhpStorm.
 * User: Uroaming
 * Date: 2019/10/30
 * Time: 11:51
 */

namespace app\backstage\model;

class Originator extends BaseModel
{
    protected $resultSetType = 'collection';

    public static $status = [ 0 => '等待应约中', 1 => '接受', 2 => '拒绝', 3 => '已取消', 4 => '已过期'];

    public function getCreateTimeAttr($time)
    {
        return $time;//返回create_time原始数据，不进行时间戳转换。
    }

    public function getStartTimeAttr($time)
    {
        return $time;//返回create_time原始数据，不进行时间戳转换。
    }
    public function getEndimeAttr($time)
    {
        return $time;//返回create_time原始数据，不进行时间戳转换。
    }
}