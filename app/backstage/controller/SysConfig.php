<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-03-19
 * Time: 09:52
 */
namespace app\backstage\controller;
use app\common\controller\Base;
use app\backstage\model\SysConfig as SCModel;
class SysConfig extends Base
{
    public function index()
    {
        $sc = new SCModel();
        $sys_config = $sc->column("value","name");
        $this->assign("sys_config",$sys_config);
        return $this->fetch();
    }
    public function addInfo()
    {
        $this->request->filter('');
        $sc= new SCModel();
        $tid = $this->request->param("id");
        $sc->where(["tid"=>$tid])->delete();
        $i=0;
        foreach ($this->request->post() as $key=>$value)
        {
            $i+=$sc->isUpdate(false)->save(["name"=> $key,"value"=>$value,"tid"=>$tid]);
        }
        return  json($i>0?["code"=>1,"msg"=>"保存成功"]: ["code"=>1,"msg"=>"保存失败"]);
    }
}
