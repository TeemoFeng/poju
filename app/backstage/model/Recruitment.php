<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-08-18
 * Time: 15:35
 */

namespace app\backstage\model;

class Recruitment extends BaseModel
{
    protected $append = ['publishtime'];

    public function getPublishtimeAttr($value,$data)
    {
        return date('Y-m-d H:i:s',$data['rel_date']);
    }
    public function setRelDateAttr($value)
    {
        return strtotime($value);
    }
}