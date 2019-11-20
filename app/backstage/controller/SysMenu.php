<?php
namespace app\backstage\controller;
use app\common\controller\Base;
use app\backstage\model\SysPermission;
class SysMenu extends Base
{
    public function index()
    {
        return $this->fetch();
    }
    public function add()
    {
        if ($this->request->isPost())
        {
            $per = new SysPermission();
            $data = $this->request->post();
            $data["sort"]=0;
            $data["state"]=0;
            $data["tid"]=0;
            $res = $per->allowField(true)->isUpdate($data["id"]==0?false:true)->save($data);
            return json($res > 0 ?["code" => 1, "msg" => "保存成功！"]:["code" => 2, "msg" => "保存失败！"]);
        }else{
            $id = $this->request->param("id");
            if ($id!=null)
            {
                $data = SysPermission::get($id);
                $this->assign("menu",$data);
            }
            return $this->fetch();
        }
    }
    public function getMenuTree()
    {
        $per = new SysPermission();
        $list = $per->order("sort","asc")->select();
        return json($list);
    }
    public function sort()
    {
        $list =[] ;
        foreach ($this->request->post() as $item=>$value) {
            array_push($list,['id'=>ltrim($item,"_"),"sort"=>$value]);
        }
        $per = new SysPermission();
        $n = $per->saveAll($list);
        return json( count($n)>0?["code" => 1, "msg" => "保存成功！","act"=>"TableTree.GetTree"]:["code" => 2, "msg" => "保存失败！"]);
    }
    public function delete()
    {
        $per = new SysPermission();
        $id = $this->request->param("id");
        return json($per::destroy($id)>0?["code"=>1,"msg"=>"该记录已删除！"]:["code"=>2,"msg"=>"没有记录被删除！"]);
    }
    public function banMenuInBatch()
    {
        $per = new SysPermission();
        $idlist=$this->request->param("idlist");
        $n = $per->where("id","in",$idlist)->setField("state",1);
        return json($n>0?["code"=>1,"msg"=>"所选菜单已禁用"]:["code"=>2,"msg"=>"当前没有菜单被禁用"]);
    }
    public function liftBanMenuInBatch()
    {
        $per = new SysPermission();
        $idlist=$this->request->param("idlist");
        $n = $per->where("id","in",$idlist)->setField("state",0);
        return json($n>0?["code"=>1,"msg"=>"所选菜单已解除禁用"]:["code"=>2,"msg"=>"当前没有菜单被解除禁用"]);
    }
    public function deleteMenuInBatch()
    {
        $per = new SysPermission();
        $idlist=$this->request->param("idlist");
        $n = $per->where("id","in",$idlist)->delete();
        return json($n>0?["code"=>1,"msg"=>"所选菜单已删除"]:["code"=>2,"msg"=>"当前没有菜单被删除"]);
    }
}