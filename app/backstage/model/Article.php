<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-03-21
 * Time: 14:37
 */

namespace app\backstage\model;
class Article extends BaseModel
{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    public function category()
    {
        return $this->belongsTo("Category",'tid');
    }
    public function userinfo()
    {
        return $this->belongsTo("UserInfo",'uid')->field('id,nickname,avatar,tags,intro');
    }
    public function setReleaseTimeAttr($value)
    {
        return strtotime($value);
    }
    public function getNextOne($model)
    {
        return $this->where('id','<',$model['id'])->where('tid','=',$model['tid'])->order('id','desc')->limit(1)->find();
    }
    public function getPreviousOne($model)
    {
        return $this->where('id','>',$model['id'])->where('tid','=',$model['tid'])->order('id','asc')->limit(1)->find();
    }

    //文章最新
    public function getTuiArticleItems($page=1)
    {
        $category = new Category();
        $data =  $this
            ->withCount('praiseLike')
            ->with('userinfo,category')
            ->where('tid','in',$category->getChildIdlist(1))
            ->order('release_time','desc')
            ->field('id,name,tid,img,release_time,views,description,uid')
            ->paginate(10,false,['page'=>$page]);
        return $data;
    }
    protected $append = ['gdate'];
    public function getGdateAttr($value,$data)
    {
       return strtotime(date('Y-m-d',$data['release_time']));
    }

    public function praiseLike()
    {
        return $this->hasMany('\app\index\model\Praise','aid')->where('tid','=','0');
    }
    public function praiseCollect()
    {
        return $this->hasMany('\app\index\model\Praise','aid')->where('tid','=','1');
    }
    public function praiseBull()
    {
        return $this->hasMany('\app\index\model\Praise','aid')->where('tid','=','2');
    }
    public function praiseBear()
    {
        return $this->hasMany('\app\index\model\Praise','aid')->where('tid','=','3');
    }
}