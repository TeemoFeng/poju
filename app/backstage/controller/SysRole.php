<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-05-24
 * Time: 17:52
 */
namespace app\backstage\controller;
use app\common\controller\Base;
use app\backstage\model\SysRole as Role;
use think\Request;
class SysRole extends Base
{
    protected $role = null;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->role = new Role();
    }
    public function items()
    {
        $res = $this->role->select();
        $this->assign("RoleList",$res);
        return $this->fetch();
    }
    public function getSelectableList()
    {
        $res = $this->role->where("state","=","0")->field("id,role_name")->select();
        return json($res);
    }
    public function add()
    {
        if ( $this->request->isPost())
        {
            $data = $this->request->post();
            return $this->role->baseSave($data);
        }else{
            $id = $this->request->param("id");
            if ($id != null) {
                $data = $this->role->get($id);
                $this->assign("model", $data);
            }
            return $this->fetch();
        }

    }
  
    public function delete()
    {
        $id = $this->request->param("id");
        $this->role->destroy($id);
        return ["code"=>1,"msg"=>"该记录已删除！"];
    }
    public function deleteinbatch()
    {
        $idlist=$this->request->param("idlist");
        $n = $this->role->where("id","in",$idlist)->delete();
        return ["code"=>1,"msg"=>"所选记录已删除！"];
    }
  
    public function allocationPermissions()
    {
       if($this->request->isPost())
       {
           $data = $this->request->post();
           $res = $this->role->get($data["id"]);
           $res->permission()->detach();
           if (!empty($data['per'])) {
               $res->permission()->attach(explode(',',$data['per']));
           }
           return json(["code"=>1,"msg"=>"保存成功！"]);
       }else{
           $id = $this->request->param("id");
           $role =  $this->role->get($id);
           $per = "";
           foreach ($role->permission as $p){
              $per.=$p->id.",";
           }
           $this->assign("per",rtrim($per,","));
           $this->assign("id",$id);
           return $this->fetch();
       }
    }

}