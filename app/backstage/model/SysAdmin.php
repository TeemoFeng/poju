<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-05-24
 * Time: 17:16
 */
namespace app\backstage\model;
class SysAdmin extends BaseModel
{
    protected $autoWriteTimestamp=true;
    protected $createTime = 'create_time';
    protected $updateTime =false;
    public function roles()
    {
        return $this->belongsToMany('SysRole',"sys_admin_role","rid","uid");
    }
}