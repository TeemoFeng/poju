<?php
namespace app\backstage\controller;
use app\common\controller\Base;
use think\Request;
use app\backstage\model\Article as Art;
use app\backstage\model\Category;
class Article extends Base
{
    protected $art = null;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->art = new Art();
    }
    public function items($id)
    {
        $cg = new Category();
        $child = $cg->getChildIdlist($id);
        if (!empty($child))
        {
            $list = $this->art->with('category')->where("tid","in",$child)->paginate();
        }else{
            $list = $this->art->with('category')->where("tid","=",$id)->paginate();
        }
        $pid = $cg->getRootId($id);
        $nav = $cg->GetTypeIsListChildByPid($pid);
        $this->assign("list",$nav);
        $this->assign("page",$list);
        $this->assign("cid",$id);
        $root = $cg->find($pid);
        $this->assign('Root',$root);
        return empty($root->list_tpl)?$this->fetch():$this->fetch($root->list_tpl);
    }
    public function add()
    {
        $this->request->filter('');
        if($this->request->isPost())
        {
            $data = $this->request->post();
            if ($data['uid']=='0'){
                return json(['code' => 2, 'msg' => '必须选择一个官方链条号']);
            }
            $data["description"] = empty($data["description"]) ? substr(strip_tags($data["html_content"]),0,225*3) :$data["description"];
            $res = $this->art->allowField(true)->isUpdate($data["id"]==0?false:true)->save($data);
            if ($res > 0) {
                return json(["code" => 1, "msg" => "保存成功！"]);
            } else {
                return json(["code" => 2, "msg" => "保存失败！"]);
            }
        }else{
            $id = $this->request->param("id");
            if ($id != null)
            {
                $model = $this->art->get($id);
                $this->assign("model",$model);
            }
            $pid = $this->request->param('pid');
            $this->assign("pid",$pid);
            $cg = new Category();
            $pmodel = $cg->find($pid);
            return empty($pmodel->add_tpl)? $this->fetch():$this->fetch($pmodel->add_tpl);
        }
    }
    public function deleteArticleInBatch()
    {
        $idlist=$this->request->param("idlist");
        $n = $this->art->where("id","in",$idlist)->delete();
        return json($n>0?["code"=>1,"msg"=>"所选记录已删除"]:["code"=>2,"msg"=>"当前没有记录被删除"]);
    }
    public function delete()
    {
        $id=$this->request->param("id");
        $n = $this->art->where("id","=",$id)->delete();
        return json($n>0?["code"=>1,"msg"=>"该条记录已删除"]:["code"=>2,"msg"=>"删除失败"]);
    }
    public function getSilbingByTid(){
        $tid = $this->request->param('tid');
        $data = $this->art->where('tid','=',$tid)->field('id,name,release_time')->select();
        return json($data);
    }

    public function attrSetting()
    {
        $act = $this->request->param('act');
        $idlist = $this->request->param("idlist");
        $n = $this->art->where("id","in",$idlist)->setField($act,1);
        return json($n>0?["code"=>1,"msg"=>"设置成功！"]:["code"=>2,"msg"=>"当前没有文章属性发生改变"]);
    }
}