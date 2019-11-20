<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-06-20
 * Time: 11:13
 */

namespace app\backstage\controller;
use app\backstage\model\Feedback as FB;
use app\common\controller\Base;
class Feedback extends Base
{
    public function items()
    {
        $fl = new FB();
        $data = $fl->select();
        $this->assign('list',$data);
        return $this->fetch();
    }
    public function delete()
    {
        $fl = new FB();
        $id = $this->request->param("id");
        return json($fl->destroy($id)>0?["code"=>1,"msg"=>"该记录已删除！"]:["code"=>1,"msg"=>"没有记录被删除！"]);
    }
    public function deleteInBatch()
    {
        $fl = new FB();
        $idlist=$this->request->param("idlist");
        $n = $fl->where("id","in",$idlist)->delete();
        return json($n>0?["code"=>1,"msg"=>"所选记录已删除"]:["code"=>2,"msg"=>"当前没有记录被删除"]);
    }
}