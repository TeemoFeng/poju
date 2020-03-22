<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-06-06
 * Time: 10:57
 */
namespace app\backstage\controller;
use app\common\controller\Base;
use app\backstage\model\Category as T;
use think\Db;

class Category extends Base
{
    public function items()
    {
         return $this->fetch();
    }
    public function getAll()
    {
        $cg = new T();
        $id = $this->request->param('id');
        $root = ['id'=>0,'name'=>'顶级栏目','pid'=>-1];
        $list = $cg->order('sort','asc')->select();
        if ($id != 0 ){
            $list = $cg->getChildByPid($id);
            $rmodel = $cg->find($id);
            if (count($list) == 1){
                $root = $list[0]->id !=$id?['id'=>$id,'pid'=>$rmodel->pid,'name'=>$rmodel->name]:null;
            }else{
                $root = ['id'=>$id,'pid'=>$rmodel->pid,'name'=>$rmodel->name];
            }

        }
        return json(['list'=>$list,'root'=>$root]);
    }
    public function add()
    {
        $this->request->filter('');
        $cg = new T();
        if ($this->request->isPost()){
            $data = $this->request->post();

            if ($data['sign_way'] == 2) {
                if (empty($data['diy_form'])) {
                    return json(['code' => 0, 'msg' => '内部报名请选择需要填写的表单项']);
                }
                $data['diy_form'] = implode(',',$data['diy_form']) .',';
            } else {
                unset($data['diy_form']);
            }

            if ($data['id']==0){
                unset($data['id']);
                return $cg->addNode($data);
            }else{
                return $cg->editNode($data);
            }
        }else{
            $id = $this->request->param("id");
            $pid = $this->request->param("pid");
            if ($id != null){
                $model = $cg->find($id);
                $this->assign("model", $model);
            }
            $this->assign('tree_pid',$pid?:0);
            return $this->fetch();
        }
    }
    public function delete()
    {
        $cg = new T();
        $id = $this->request->param('id');
        $isHasChild = $cg->where('pid', '=',$id)->whereNull('delete_time')->count();
        if($isHasChild>0){
            return json(["code" => 2, "msg" => "此分类有子类无法删除！"]);
        }
        $row = $cg->find($id);
        if ($row) {
            $res = $row->delete();
            return json($res > 0 ?["code" => 1, "msg" => "删除成功！"]:["code" => 2, "msg" => "删除失败！"]);
        }
    }
    public function getChild($pid){
        $cg = new T();
        return json($cg->getChildByPid($pid));
    }
    public function GetTypeIsListChildByPid($pid)
    {
        $cg = new T();
        return json($cg->GetTypeIsListChildByPid($pid));
    }
    public function sort()
    {
        $list =[] ;
        foreach ($this->request->post() as $item=>$value) {
            array_push($list,['id'=>ltrim($item,"_"),"sort"=>$value]);
        }
        $t = new T();
        $n = $t->saveAll($list);
        return json( count($n)>0?["code" => 1, "msg" => "保存成功！","act"=>"TableTree.GetTree"]:["code" => 2, "msg" => "保存失败！"]);
    }
    public function getAllList()
    {
        $cg = new T();
        return $cg->field('id,pid,root_path,name')->order('sort','asc')->select();
    }
    public function attrSetting()
    {
        $cg = new T();
        $act = $this->request->param('act');
        $idlist = $this->request->param("idlist");
        $prop = $this->request->param('prop');
        $n = $cg->where("id","in",$idlist)->setField($act,$prop);
        return json($n>0?["code"=>1,"msg"=>"设置成功！"]:["code"=>2,"msg"=>"当前没记录属性发生改变"]);
    }

    public function getElemItems()
    {
        //从官网后去填写标签
        $this->db_app = Db::connect('database_morketing');
        $items = $this->db_app->table('diy_form')->select();
        return json($items);
    }
}