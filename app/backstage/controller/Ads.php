<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-07-22
 * Time: 16:48
 */

namespace app\backstage\controller;
use app\common\controller\Base;
use app\backstage\model\Ads as Ad;
use app\backstage\model\Category;
class Ads extends  Base
{
    public function items()
    {
        $ads = new Ad();
        $category = new Category();
        $sid = $this->request->param('sid');
        if (!empty($sid))
        {
            $list = $ads->with('category')->where('tid','=',$sid)->order('displayorder','asc')->paginate(20);
        }else{
            $list = $ads->with('category')->order('displayorder','asc')->paginate(20);
        }
        $nav = $category->order('id desc')->select();
        $this->assign("list",$nav);
        $this->assign("page",$list);
        $this->assign("key",$sid);
        return $this->fetch();
    }
    public function add()
    {
        $this->request->filter('');
        $ads = new Ad();
        if ( $this->request->isPost()){
            return $ads->baseSave($this->request->post());
        }else{
            $id = $this->request->param("id");
            if ($id != null)
            {
                $model = $ads->find($id);
                $this->assign("model",$model);
            }
            return $this->fetch();
        }
    }
    public function delete()
    {
        $ads = new Ad();
        $id = $this->request->param("id");
        return json($ads->destroy($id)>0?["code"=>1,"msg"=>"该记录已删除！"]:["code"=>1,"msg"=>"没有记录被删除！"]);
    }
    public function deleteInBatch()
    {
        $ads = new Ad();
        $idlist=$this->request->param("idlist");
        $n = $ads->where("id","in",$idlist)->delete();
        return json($n>0?["code"=>1,"msg"=>"所选记录已删除"]:["code"=>2,"msg"=>"当前没有记录被删除"]);
    }
    public function sort()
    {
        $list =[] ;
        foreach ($this->request->post() as $item=>$value) {
            array_push($list,['id'=>ltrim($item,"_"),"displayorder"=>$value]);
        }
        $ads = new Ad();
        $n = $ads->saveAll($list);
        return json( count($n)>0?["code" => 1, "msg" => "保存成功！"]:["code" => 2, "msg" => "保存失败！"]);
    }
}