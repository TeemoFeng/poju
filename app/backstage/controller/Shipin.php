<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-12-03
 * Time: 14:14
 */

namespace app\backstage\controller;
use app\common\controller\Base;
use app\backstage\model\Video;
use app\backstage\model\Category;
class Shipin extends Base
{
    public function items()
    {
        $category = new Category();
        $video = new Video();
        $sid = $this->request->param('sid');
        if (!empty($sid))
        {
            $list = $video->with('category')->where('sid','=',$sid)->order('sort','asc')->paginate(20);
        }else{
            $list = $video->with('category')->order('sort','asc')->paginate(20);
        }
        $nav = $category->order('id desc')->select();
        $this->assign("key",$sid);
        $this->assign("list",$nav);
        $this->assign("page",$list);
        return $this->fetch();
    }
    public function add()
    {
        $this->request->filter('');
        $agenda = new Video();
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
        $agenda = new Video();
        $idlist=$this->request->param("idlist");
        $n = $agenda->where("id","in",$idlist)->delete();
        return json($n>0?["code"=>1,"msg"=>"所选记录已删除"]:["code"=>2,"msg"=>"当前没有记录被删除"]);
    }
    public function delete()
    {
        $agenda = new Video();
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
        $agenda = new Video();
        $n = $agenda->saveAll($list);
        return json( count($n)>0?["code" => 1, "msg" => "保存成功！"]:["code" => 2, "msg" => "保存失败！"]);
    }
}