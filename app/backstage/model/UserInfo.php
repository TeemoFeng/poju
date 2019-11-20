<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-08-22
 * Time: 16:44
 */
namespace app\backstage\model;
class UserInfo extends BaseModel
{
    protected $autoWriteTimestamp=true;

    public function getOfficialUser()
    {
        return $this->field('id,nickname')->where('tid','=',2)->order('id','desc')->select();
    }
    public function scArticle()
    {
        return $this->belongsToMany('Article','praise','aid','uid')->wherePivot('tid','=',1);
    }
    public function getAvatarAttr($value)
    {
        return $value?:'/static/images/default_avatar.jpg';
    }
    public function isNotReg($account,$field)
    {
        $userModel = $this->where($field,'=',$account)->find();
        if (empty($userModel)) {
            return true;
        }else{
            return false;
        }
    }
}