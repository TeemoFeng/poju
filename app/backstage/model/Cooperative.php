<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-09-14
 * Time: 14:09
 */

namespace app\backstage\model;

class Cooperative extends BaseModel
{
    public function category()
    {
        return $this->belongsTo("Category",'sid');
    }
}