<?php
namespace app\backstage\controller;
use think\Session;
use think\Controller;
use app\index\controller\Digg;
use app\backstage\model\SysAdmin;
class Login extends Controller
{
   public function index()
   {
     return $this->fetch("login2");
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

    //手机号登录
    public function newCheck()
    {
        $post = strtolower($this->request->post("code"));
        $account = $this->request->post("mobile");
        $sms_code = $this->request->post("sms_code");
        if (empty($sms_code)) {
            return json(["code"=>5,"msg"=>"请输入短信验证码"]);
        }
        if ($sms_code != session('mobileCode')) {
            $data = ["code"=>5,"msg"=>"手机验证码错误"];
            return json($data);
        }
        if ($post==Session::get("code"))
        {
            $sa = new SysAdmin();
            $admin = $sa->where("tel","=",$account)->find();
            if ($admin != null){
                if ($admin->state==1){
                    return json(["code" => 5,"msg"=>"当前账号被禁止登入！"]);
                }else{
                    Session::set("UserInfo",$admin);
                    return json(["code"=>6,"msg"=>"登陆成功，正在跳转...","url"=>url("/backstage/console")]);
                }
            }else{
                return json(["code"=>5,"msg"=>"您还不是管理员，请先联系系统管理员"]);
            }
        }else{
            $data = ["code"=>5,"msg"=>"验证码错误"];
            return json($data);
        }

    }

    //发送验证码
    public function sendSms()
    {
        $mobile = $this->request->post('mobile');
        $sa = new SysAdmin();
        $admin = $sa->where("tel","=",$mobile)->find();
        if(empty($admin))
        {
            return json(['code'=>2,'msg'=>'您还不是管理员，请先联系系统管理员','model'=>'msg']);
        }
        $aliSms = new Digg();
        return $aliSms->sendSms($mobile);
    }

}