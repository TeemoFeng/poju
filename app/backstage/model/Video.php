<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-12-03
 * Time: 14:13
 */

namespace app\backstage\model;

class Video extends BaseModel
{
    public function category()
    {
        return $this->belongsTo("Category",'sid');
    }

    public function setStartTimeAttr($value)
    {
        return strtotime($value);
    }
    protected $append = ['g_time'];
    public function getGTimeAttr($value,$data)
    {
        return strtotime(date('Y-m-d',$data['start_time']));
    }
}