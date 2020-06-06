<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-06-06
 * Time: 10:57
 */
namespace app\backstage\controller;
use app\common\controller\Base;
use app\backstage\model\RecommendRule as T;
class RecommendRule extends Base
{
    public function items()
    {
        $pid = $this->request->param('pid');
        if(empty($pid)){
            return $this->fetch();
        }else{
            $this->assign(['p'=>$pid]);
            return $this->fetch('list');
        }
    }
    public function getAll()
    {
        $cg = new T();
        $id = $this->request->param('id');
        $root = ['id'=>0,'name'=>'顶级栏目','pid'=>-1];
        $list = $cg->order('sort','asc')->select();
        if ($id != 0 ){
            $list = $cg->getChildByPid($id);
            $rmodel = $cg->find($id);
            if (count($list) == 1){
                $root = $list[0]->id !=$id?['id'=>$id,'pid'=>$rmodel->pid,'name'=>$rmodel->name]:null;
            }else{
                $root = ['id'=>$id,'pid'=>$rmodel->pid,'name'=>$rmodel->name];
            }

        }
        return json(['list'=>$list,'root'=>$root]);
    }

    public function getItems(){
        $cg = new T();
        $pid = $this->request->param('pid');
        $root = $cg->find($pid);
        $list = $cg->getListWithRoot($pid);
        return ['list'=>$list,'root'=>$root];
    }
    public function add()
    {
        $cg = new T();
        if ($this->request->isPost()){
            $data = $this->request->post();
            if ($data['id']==0){
                unset($data['id']);
                return $cg->addNode($data);
            }else{
                return $cg->editNode($data);
            }
        }else{
            $id = $this->request->param("id");
            $pid = $this->request->param("pid");
            if ($id != null){
                $this->assign("model",$cg->find($id));
            }
            $this->assign('tree_pid',$pid?:0);
            return $this->fetch();
        }
    }
    public function delete()
    {
        $cg = new T();
        $id = $this->request->param('id');
        $isHasChild = $cg->where('pid', '=',$id)->whereNull('delete_time')->count();
        if($isHasChild>0){
            return json(["code" => 2, "msg" => "此分类有子类无法删除！"]);
        }
        $row = $cg->find($id);
        if ($row) {
            $res = $row->delete();
            return json($res > 0 ?["code" => 1, "msg" => "删除成功！"]:["code" => 2, "msg" => "删除失败！"]);
        }
    }
    public function getChild($pid){
        $cg = new T();
        return json($cg->getChildByPid($pid));
    }
    public function GetTypeIsListChildByPid($pid)
    {
        $cg = new T();
        $result =  json($cg->GetTypeIsListChildByPid($pid));
        return $result;
    }
    public function sort()
    {
        $list =[] ;
        foreach ($this->request->post() as $item=>$value) {
            array_push($list,['id'=>ltrim($item,"_"),"sort"=>$value]);
        }
        $t = new T();
        $n = $t->saveAll($list);
        return json( count($n)>0?["code" => 1, "msg" => "保存成功！","act"=>"TableTree.GetTree"]:["code" => 2, "msg" => "保存失败！"]);
    }
}