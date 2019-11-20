<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-08-26
 * Time: 16:54
 */

namespace app\backstage\controller;
use app\common\controller\Base;
use app\backstage\model\Guest as G;
use app\backstage\model\Category;
class Guest extends Base
{
    public function items()
    {
        $guest = new G();
        $category = new Category();
        $sid = $this->request->param('sid');
        if (!empty($sid))
        {
            $list = $guest->with('category')->where('sid','=',$sid)->order('sort','asc')->paginate(20);
        }else{
            $list = $guest->with('category')->order('sort','asc')->paginate(20);
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
        $guest = new G();
        if ( $this->request->isPost()){
            $data = $this->request->post();
            if(isset($data['is_new_power'])){
                $data['is_new_power'] = 1;
            }else{
                $data['is_new_power'] = 0;
            }
            return $guest->baseSave($data);
        }else{
            $id = $this->request->param("id");
            if ($id != null)
            {
                $model = $guest->find($id);
                $this->assign("model",$model);
            }
            return $this->fetch();
        }
    }
    public function deleteInBatch()
    {
        $guest = new G();
        $idlist=$this->request->param("idlist");
        $n = $guest->where("id","in",$idlist)->delete();
        return json($n>0?["code"=>1,"msg"=>"所选记录已删除"]:["code"=>2,"msg"=>"当前没有记录被删除"]);
    }
    public function delete()
    {
        $guest = new G();
        $id=$this->request->param("id");
        $n = $guest->where("id","=",$id)->delete();
        return json($n>0?["code"=>1,"msg"=>"该条记录已删除"]:["code"=>2,"msg"=>"删除失败"]);
    }
    public function sort()
    {
        $list =[] ;
        foreach ($this->request->post() as $item=>$value) {
            array_push($list,['id'=>ltrim($item,"_"),"sort"=>$value]);
        }
        $guest = new G();
        $n = $guest->saveAll($list);
        return json( count($n)>0?["code" => 1, "msg" => "保存成功！"]:["code" => 2, "msg" => "保存失败！"]);
    }
}