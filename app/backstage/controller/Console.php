<?php
namespace app\backstage\controller;
use app\common\controller\Base;
use think\Session;
use \think\Db;
class Console extends Base
{
    public function index()
    {
        $this->assign("AdminInfo",$this->AdminInfo);
        $this->assign("menu",$this->getCurrentAdminAuthMenu());
        return $this->fetch();
    }
    public function logout()
    {
        Session::delete("UserInfo");
        return json(["code"=>16,"msg"=>"正在注销账户...","url"=>url('/backstage/login')]);
    }
    public function breathe()
    {
        return json(["timestamp"=>time(),"user"=>$this->AdminInfo->account]);
    }
    public function getCurrentAdminAuthMenu()
    {
        $rids = $this->AdminInfo->roles()->column('rid');
        $pids = Db::table("sys_role_permission")
            ->whereIn("rid",$rids)
            ->group("pid")
            ->column("pid");
        $menuList = Db::table("sys_permission")->whereIn("id",$pids)->select();
        return json_encode($menuList);
    }
}