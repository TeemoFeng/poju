<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-08-26
 * Time: 15:01
 */

namespace app\backstage\controller;
use app\common\controller\Base;
use app\backstage\model\Category;
use app\backstage\model\Agenda as Ag;
class Agenda extends Base
{
    public function items($id = 14)
    {
        $agenda = new Ag();
        $category = new Category();
        $sid = $this->request->param('sid');
        if (!empty($sid))
        {
            $list = $agenda->with('category')->where('sid','=',$sid)->order('sort','asc')->paginate(20);
        }else{
            $list = $agenda->with('category')->order('sort','asc')->paginate(20);
        }
        $nav = $category->select();
        $this->assign("list",$nav);
        $this->assign("page",$list);
        $this->assign("key",$sid);
        return $this->fetch();
    }
    public function add()
    {
        $this->request->filter('');
        $agenda = new Ag();
        if ( $this->request->isPost()){
            return $agenda->baseSave($this->request->post());
        }else{
            $id = $this->request->param("id");
            if ($id != null)
            {
                $model = $agenda->find($id);
                $this->assign("model",$model);
            }
            return $this->fetch();
        }
    }
    public function deleteInBatch()
    {
        $agenda = new Ag();
        $idlist=$this->request->param("idlist");
        $n = $agenda->where("id","in",$idlist)->delete();
        return json($n>0?["code"=>1,"msg"=>"所选记录已删除"]:["code"=>2,"msg"=>"当前没有记录被删除"]);
    }
    public function delete()
    {
        $agenda = new Ag();
        $id=$this->request->param("id");
        $n = $agenda->where("id","=",$id)->delete();
        return json($n>0?["code"=>1,"msg"=>"该条记录已删除"]:["code"=>2,"msg"=>"删除失败"]);
    }
    public function sort()
    {
        $list =[] ;
        foreach ($this->request->post() as $item=>$value) {
            array_push($list,['id'=>ltrim($item,"_"),"sort"=>$value]);
        }
        $agenda = new Ag();
        $n = $agenda->saveAll($list);
        return json( count($n)>0?["code" => 1, "msg" => "保存成功！"]:["code" => 2, "msg" => "保存失败！"]);
    }
}