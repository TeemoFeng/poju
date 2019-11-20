<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-08-18
 * Time: 15:36
 */

namespace app\backstage\controller;

use app\common\controller\Base;
use app\backstage\model\Recruitment as Re;
class Recruitment extends Base
{
    public function index()
    {
        $re = new Re();
        $list = $re->select();
        $this->assign("Items",$list);
        return $this->fetch();
    }
    public function add()
    {
        $this->request->filter('');
        $re = new Re();
        if ( $this->request->isPost()){
            return $re->baseSave($this->request->post());
        }else{
            $id = $this->request->param("id");
            if ($id != null)
            {
                $model = $re->find($id);
                $this->assign("model",$model);
            }
            return $this->fetch();
        }
    }
    public function delete()
    {
        $re = new Re();
        $id = $this->request->param("id");
        return json($re->destroy($id)>0?["code"=>1,"msg"=>"该记录已删除！"]:["code"=>1,"msg"=>"没有记录被删除！"]);
    }
    public function deleteInBatch()
    {
        $re = new Re();
        $idlist=$this->request->param("idlist");
        $n = $re->where("id","in",$idlist)->delete();
        return json($n>0?["code"=>1,"msg"=>"所选记录已删除"]:["code"=>2,"msg"=>"当前没有记录被删除"]);
    }

}