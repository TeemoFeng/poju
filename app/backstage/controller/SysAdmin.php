<?php
namespace app\backstage\controller;
use app\backstage\model\SysAdmin as Admin;
use think\Request;
use app\common\controller\Base;
class SysAdmin extends Base
{
    protected $admin = null;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->admin = new Admin();
    }

    public function index()
    {
        $date =$this->admin->select();
        $this->assign("admin_list",$date);
        return $this->fetch();
    }
    public function add()
    {
        if ($this->request->isPost()) {
            $postData = $this->request->post();
            $admin = new Admin();
            if($postData['id']==0) {
                $postData["password"] = generateMD5WithSalt($postData["password"]);
                $postData['tid'] = 2;
                Admin::create($postData,true);
                $admin['id'] = Admin::get(['account'=>$postData['account']])['id'];
                $admin->roles()->detach();
                if (!empty($postData['rid'])) {
                    $admin->roles()->attach(explode(',',$postData['rid']));
                }
                return ['code'=>1,'msg'=>'账号创建成功！'];
            }else{
                $adminId = $postData['id'];
                unset($postData['password']);
                Admin::update($postData,['id'=>$adminId],true);
                $admin['id'] = $adminId;
                $admin->roles()->detach();
                if (!empty($postData['rid'])) {
                    $admin->roles()->attach(explode(',',$postData['rid']));
                }
                return ['code'=>1,'msg'=>'更新成功！'];
            }
        } else {
            $id = $this->request->param("id");
            if (!empty($id))
            {
                $data = Admin::get($id);
                $this->assign("admin",$data);
            }
            return $this->fetch();
        }
    }
    public function check_account()
    {
        $a = $this->request->param();
        $admin = $this->admin -> where("account","eq",$a["account"])->where("id","<>",$a["id"])->find();
        return $admin ? false : true;
    }
    public function delete()
    {
        $id = $this->request->param("id");
        return json($this->admin->destroy($id)>0?["code"=>1,"msg"=>"该记录已删除！"]:["code"=>1,"msg"=>"没有记录被删除！"]);
    }
    public function banAdminInBatch()
    {
        $idlist=$this->request->param("idlist");
        $n = $this->admin->where("id","in",$idlist)->setField("state",1);
        return json($n>0?["code"=>1,"msg"=>"所选管理员已禁用"]:["code"=>2,"msg"=>"当前没有管理员被禁用"]);
    }
    public function liftBanAdminInBatch()
    {
        $idlist=$this->request->param("idlist");
        $n = $this->admin->where("id","in",$idlist)->setField("state",0);
        return json($n>0?["code"=>1,"msg"=>"所选管理员已解除禁用"]:["code"=>2,"msg"=>"当前没有管理员被解除禁用"]);
    }
    public function deleteAdminInBatch()
    {
        $idlist=$this->request->param("idlist");
        $n = $this->admin->where("id","in",$idlist)->delete();
        return json($n>0?["code"=>1,"msg"=>"所选管理员已删除"]:["code"=>2,"msg"=>"当前没有管理员被删除"]);
    }
    public function changepwd()
    {
        if ($this->request->isPost())
        {
            $old = $this->request->post("oldPwd");
            $newPwd = $this->request->post("newPwd");
            $pwd = $this->request->post("pwd");
            if (verifyMD5Code($old,$this->AdminInfo->password)){
                if ($newPwd == $pwd){
                    $n = $this->admin->where("id","=",$this->AdminInfo->id)->setField("password",generateMD5WithSalt($pwd));
                    return json($n>0?["code"=>6,"msg"=>"密码修改成功！"]:["code"=>5,"msg"=>"密码修改失败"]);
                }else{
                    return json(["code"=>5,"msg"=>"两次输入的密码不一致！","act"=>"$('[type=\'password\']').val('')"]);
                }
            }else{
                return json(["code"=>5,"msg"=>"原始密码输入错误!","act"=>"$('[type=\'password\']').val('')"]);
            }
        }else{
            return $this->fetch();
        }
    }
}