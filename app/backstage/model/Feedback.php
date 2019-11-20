<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-06-20
 * Time: 09:43
 */

namespace app\backstage\model;
class Feedback extends BaseModel
{
    protected $autoWriteTimestamp=true;
    protected $createTime = 'subdate';
    protected $updateTime =false;
}