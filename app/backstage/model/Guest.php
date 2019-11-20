<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-08-26
 * Time: 16:50
 */
namespace app\backstage\model;
class Guest extends BaseModel
{
    public function category()
    {
        return $this->belongsTo("Category",'sid');
    }
}