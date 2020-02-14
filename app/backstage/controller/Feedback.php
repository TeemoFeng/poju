<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-06-20
 * Time: 11:13
 */

namespace app\backstage\controller;
use app\backstage\model\Feedback as FB;
use app\common\controller\Base;
use app\backstage\model\Category;
class Feedback extends Base
{
    public function items()
    {
        $fl = new FB();
        $category = new Category();
        $sid = $this->request->param('sid');
        if (!empty($sid))
        {
            $list = $fl->with('category')->where('sid','=',$sid)->order('id','asc')->paginate(20);
        }else{
            $list = $fl->with('category')->order('id','asc')->paginate(20);
        }
        $nav = $category->select();
        $this->assign("list",$nav);
        $this->assign("page",$list);
        $this->assign("key",$sid);
        return $this->fetch();
    }
    public function delete()
    {
        $fl = new FB();
        $id = $this->request->param("id");
        return json($fl->destroy($id)>0?["code"=>1,"msg"=>"该记录已删除！"]:["code"=>1,"msg"=>"没有记录被删除！"]);
    }
    public function deleteInBatch()
    {
        $fl = new FB();
        $idlist=$this->request->param("idlist");
        $n = $fl->where("id","in",$idlist)->delete();
        return json($n>0?["code"=>1,"msg"=>"所选记录已删除"]:["code"=>2,"msg"=>"当前没有记录被删除"]);
    }
}