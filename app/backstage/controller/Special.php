<?php
/**
 * Created by PhpStorm.
 * User: Uroaming
 * Date: 2020/3/21
 * Time: 21:34
 */
namespace app\backstage\controller;
use app\common\controller\Base;
use app\backstage\model\Special as SpecialModel;

class Special extends Base
{
    public function items()
    {
        $t = $this->request->param('t', 1);
        $list = SpecialModel::where('tid','in',$t)->order('displayorder asc')->paginate(20);
        $this->assign('items',$list);
        return $this->fetch();
    }
    public function add()
    {
        if ( $this->request->isPost()){
            $postData = $this->request->post();
            if($postData['id']==0){
                SpecialModel::create($postData,true);
            }else{
                SpecialModel::update($postData,['id'=>$postData['id']],true);
            }
            return ['code'=>1,'msg'=>'保存成功！'];
        }else{
            $id = $this->request->param("id");
            $t = $this->request->param('t');
            $tpl ='';
            if(!empty($t)){
                $tpl = $this->editTpl[$t];
            }
            if (!empty($id)){
                $model = SpecialModel::get($id);
                $this->assign("model",$model);
            }
            return $this->fetch($tpl);
        }
    }
    public function delete()
    {
        $ads = new SpecialModel();
        $id = $this->request->param("id");
        return json($ads->destroy($id)>0?["code"=>1,"msg"=>"该记录已删除！"]:["code"=>1,"msg"=>"没有记录被删除！"]);
    }
    public function deleteInBatch()
    {
        $ads = new SpecialModel();
        $idlist=$this->request->param("idlist");
        $n = $ads->where("id","in",$idlist)->delete();
        return json($n>0?["code"=>1,"msg"=>"所选记录已删除"]:["code"=>2,"msg"=>"当前没有记录被删除"]);
    }
    public function attrSetting()
    {
        $act = $this->request->param('act');
        $val = $this->request->param('prop');
        $idlist = $this->request->param("idlist");
        SpecialModel::where("id","in",$idlist)->setField($act,$val);
        return ["code"=>1,"msg"=>"设置成功！"];
    }
    public function sort()
    {
        $list =[] ;
        foreach ($this->request->post() as $item=>$value) {
            array_push($list,['id'=>ltrim($item,"_"),"displayorder"=>$value]);
        }
        $ads = new SpecialModel();
        $n = $ads->saveAll($list);
        return json( count($n)>0?["code" => 1, "msg" => "保存成功！"]:["code" => 2, "msg" => "保存失败！"]);
    }
}
