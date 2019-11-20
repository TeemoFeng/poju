<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-05-24
 * Time: 17:14
 */

namespace app\backstage\model;
class SysRole extends BaseModel
{
    public function permission()
    {
        return $this->belongsToMany('SysPermission',"sys_role_permission","pid","rid");
    }
}