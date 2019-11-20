<?php
/**
 * Created by PhpStorm.
 * User: Uroaming
 * Date: 2019/10/28
 * Time: 14:21
 */
namespace app\backstage\controller;
use app\common\controller\Base;
use app\backstage\model\Space as SpaceModel;
use app\backstage\model\Category;

class Space extends Base
{
    //场地列表
    public function items()
    {
        $list = SpaceModel::order('id','desc')->paginate()
            ->each(function ($item){
                $name = Category::where('id', $item->sid)->value('name');
                $item->category = $name ?: '未找到'; //会议名称
        });
        $this->assign(['items'=>$list]);
        return $this->fetch();
    }

    //添加/编辑 场地
    public function add()
    {
        $this->request->filter('');
        if ($this->request->isPost()){
            $postData = $this->request->post();
            if (empty($postData['sid'])) {
                return json(['code' => 0, 'msg' => '请选择会议']);
            }
            if (empty($postData['name'])) {
                return json(['code' => 0, 'msg' => '请填写场地名称']);
            }
            if (empty($postData['address'])) {
                return json(['code' => 0, 'msg' => '请填写场地地址']);
            }
            if (empty($postData['mark'])) {
                return json(['code' => 0, 'msg' => '请填写桌子数量']);
            }
            if (empty($postData['contain'])) {
                return json(['code' => 0, 'msg' => '请填写容纳人数']);
            }

            if (empty($postData['id'])) {
                //新增
                $res = SpaceModel::create($postData,true);
            } else {
                //编辑
                $res = SpaceModel::update($postData,['id'=>$postData['id']],true);
            }

            if ($res !== false) {
                return json(['code'=>1,'msg'=>'保存成功！']);
            } else {
                return json(['code' => 0, 'msg' => '操作失败，请重试']);
            }
        } else {
            $id = $this->request->param("id");
            if ($id != null) {
                $model = SpaceModel::get($id);
                $this->assign("model",$model);
            }
            return $this->fetch();
        }

    }

    //删除
    public function delete()
    {
        $id = $this->request->param("id");
        $n = SpaceModel::where("id","=", $id)->delete();
        if ($n === false) {
            return json(["code" => 0,"msg" => "删除失败"]);
        }
        return json(["code" => 1,"msg" => "该条记录已删除"]);
    }

    //批量删除
    public function deleteInBatch()
    {
        $idlist = $this->request->param("idlist");
        $n = SpaceModel::where("id","in",$idlist)->delete();
        if ($n === false) {
            return json(["code" => 0,"msg" => "删除失败"]);
        }
        return json(["code" => 1,"msg" => "所选记录已删除"]);
    }
}