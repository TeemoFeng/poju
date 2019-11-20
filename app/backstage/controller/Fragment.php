<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-06-22
 * Time: 14:48
 */
namespace app\backstage\controller;
use app\backstage\model\Fragment as F;
use app\common\controller\Base;

class Fragment extends Base
{
    public function items()
    {
        $f = new F();
        $list = $f->select();
        $this->assign("Items",$list);
        return $this->fetch();
    }
    public function add()
    {
        $this->request->filter('');
        $f = new F();
        if ( $this->request->isPost()){
         return $f->baseSave($this->request->post());
        }else{
            $id = $this->request->param("id");
            if ($id != null)
            {
                $model = $f->find($id);
                $this->assign("model",$model);
            }
            return $this->fetch();
        }
    }
    public function delete()
    {
        $f = new F();
        $id = $this->request->param("id");
        return json($f->destroy($id)>0?["code"=>1,"msg"=>"该记录已删除！"]:["code"=>1,"msg"=>"没有记录被删除！"]);
    }
    public function deleteInBatch()
    {
        $f = new F();
        $idlist=$this->request->param("idlist");
        $n = $f->where("id","in",$idlist)->delete();
        return json($n>0?["code"=>1,"msg"=>"所选记录已删除"]:["code"=>2,"msg"=>"当前没有记录被删除"]);
    }

}