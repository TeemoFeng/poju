<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-08-26
 * Time: 15:01
 */

namespace app\backstage\model;

class Agenda extends BaseModel
{
    public function category()
    {
        return $this->belongsTo("Category",'sid');
    }
    public function setStartdateAttr($value)
    {
        return strtotime($value);
    }
}