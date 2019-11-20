<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-09-14
 * Time: 14:09
 */
namespace app\backstage\controller;
use app\backstage\model\Cooperative as Cooper;
use app\common\controller\Base;
class Cooperative extends Base
{
    public function Items()
    {
        $cooper = new Cooper();
        $list = $cooper->with('category')->order('sort','asc')->select();
        $this->assign('Items',$list);
        return $this->fetch();
    }
    public function add()
    {
        $this->request->filter('');
        $cooper = new Cooper();
        if ( $this->request->isPost()){
            return $cooper->baseSave($this->request->post());
        }else{
            $id = $this->request->param("id");
            if ($id != null)
            {
                $model = $cooper->find($id);
                $this->assign("model",$model);
            }
            return $this->fetch();
        }
    }
    public function delete()
    {
        $cooper = new Cooper();
        $id = $this->request->param("id");
        return json($cooper->destroy($id)>0?["code"=>1,"msg"=>"该记录已删除！"]:["code"=>1,"msg"=>"没有记录被删除！"]);
    }
    public function deleteInBatch()
    {
        $cooper = new Cooper();
        $idlist=$this->request->param("idlist");
        $n = $cooper->where("id","in",$idlist)->delete();
        return json($n>0?["code"=>1,"msg"=>"所选记录已删除"]:["code"=>2,"msg"=>"当前没有记录被删除"]);
    }
    public function sort()
    {
        $list =[] ;
        foreach ($this->request->post() as $item=>$value) {
            array_push($list,['id'=>ltrim($item,"_"),"sort"=>$value]);
        }
        $cooper = new Cooper();
        $n = $cooper->saveAll($list);
        return json( count($n)>0?["code" => 1, "msg" => "保存成功！"]:["code" => 2, "msg" => "保存失败！"]);
    }

}