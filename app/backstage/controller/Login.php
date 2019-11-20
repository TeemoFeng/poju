<?php
namespace app\backstage\controller;
use think\Session;
use think\Controller;
use app\backstage\model\SysAdmin;
class Login extends Controller
{
   public function index()
   {
     return $this->fetch("login");
   }
   public function check()
   {
       $post = strtolower($this->request->post("code"));
       $account = $this->request->post("account");
       $password = $this->request->post("password");
       if ($post==Session::get("code"))
       {
            $sa = new SysAdmin();
            $admin = $sa->where("account","=",$account)->find();
            if ($admin != null && verifyMD5Code($password,$admin["password"])){
               if ($admin->state==1){
                   return json(["code" => 5,"msg"=>"当前账号被禁止登入！"]);
               }else{
                   Session::set("UserInfo",$admin);
                   return json(["code"=>6,"msg"=>"登陆成功，正在跳转...","url"=>url("/backstage/console")]);
               }
            }else{
               return json(["code"=>5,"msg"=>"账号或密码错误！"]);
            }
       }else{
           $data = ["code"=>5,"msg"=>"验证码错误"];
           return json($data);
       }

   }
}