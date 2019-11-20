<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-05-24
 * Time: 17:19
 */

namespace app\backstage\model;
use think\Model;

class BaseModel extends Model
{
    public function baseSave($data)
    {
       $res = $this->allowField(true)->isUpdate($data["id"] == 0 ? false : true)->save($data);
        if ($res > 0) {
            return json(["code" => 1, "msg" => "保存成功！"]);
        } else {
            return json(["code" => 2, "msg" => "保存失败！"]);
        }
    }
}