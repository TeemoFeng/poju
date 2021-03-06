<?php
/**
 * Created by PhpStorm.
 * User: Uroaming
 * Date: 2020/1/10
 * Time: 9:26
 */

namespace app\backstage\controller;
use app\common\controller\Base;
use app\backstage\model\Review as ReviewModel;
class Review extends Base
{
    //会议列表
    public function items()
    {

        $list = ReviewModel::order('sort ASC')->paginate(20);

        $this->assign("items", $list);
        return $this->fetch();
    }

    //添加推荐
    public function add()
    {
        if ($this->request->isPost()){
            $postData = $this->request->post();
            if (empty($postData['title'])) {
                return json(['code' => 0, 'msg' => '请填写标题']);
            }
            if (empty($postData['tag'])) {
                return json(['code' => 0, 'msg' => '请填写标签']);
            }
            if (empty($postData['profile'])) {
                return json(['code' => 0, 'msg' => '请填写简介']);
            }
            if (empty($postData['img'])) {
                return json(['code' => 0, 'msg' => '请上传图片']);
            }
            if (empty($postData['jump_url'])) {
                return json(['code' => 0, 'msg' => '请填写跳转链接']);
            }
            if (empty($postData['sort'])) {
                return json(['code' => 0, 'msg' => '请填写展示顺序']);
            }
            if (!is_numeric($postData['sort'])) {
                return json(['code' => 0, 'msg' => '展示顺序格式错误']);
            }
            if (empty($postData['id'])) {
                //新增
                $res = ReviewModel::create($postData,true);
            } else {
                //编辑
                $res = ReviewModel::update($postData,['id'=>$postData['id']],true);
            }

            if ($res !== false) {
                return json(['code'=>1,'msg'=>'保存成功！']);
            } else {
                return json(['code' => 0, 'msg' => '操作失败，请重试']);
            }
        } else {
            $id = $this->request->param("id");
            if ($id != null) {
                $model = ReviewModel::get($id);
                $this->assign("model",$model);
            }
            return $this->fetch();
        }
    }

    //删除一个近期推荐
    public function delete()
    {
        $id = $this->request->param('id');
        $res = ReviewModel::where(['id' => $id])->delete();
        if ($res === false) {
            return json(["code" => 2, "msg" => "删除失败，请重试！"]);
        }

        return json(["code" => 1, "msg" => "删除成功"]);
    }

    //批量删除
    public function deleteInBatch()
    {
        $summitBanner = new ReviewModel();
        $idlist=$this->request->param("idlist");
        $n = $summitBanner->where("id","in",$idlist)->delete();
        return json($n>0?["code"=>1,"msg"=>"所选记录已删除"]:["code"=>2,"msg"=>"当前没有记录被删除"]);
    }

    //排序
    public function sort()
    {
        $list =[] ;
        foreach ($this->request->post() as $item => $value) {
            array_push($list,['id' => ltrim($item,"_"),"sort" => $value]);
        }
        $summitBanner = new ReviewModel();
        $n = $summitBanner->saveAll($list);
        return json( count($n)>0?["code" => 1, "msg" => "保存成功！"]:["code" => 2, "msg" => "保存失败！"]);
    }

    public function status()
    {
        $id = $this->request->param('id');
        $status = $this->request->param('status');
        $res = ReviewModel::where(['id' => $id])->update(['status' => $status]);
        if ($res === false) {
            return json(["code" => 2, "msg" => "操作失败"]);
        }

        return json(["code" => 1, "msg" => "操作成功"]);
    }

    //批量上线所选记录
    public function onlineInBatch()
    {
        $summitBanner = new ReviewModel();
        $idlist=$this->request->param("idlist");
        $n = $summitBanner->where("id","in",$idlist)->update(['status' => 2]);
        return json($n !==false ? ["code" => 1,"msg" => "所选记录已上线"] : ["code" => 2,"msg" => "操作失败，请重试"]);
    }

    //批量下线所选记录
    public function offlineInBatch()
    {
        $summitBanner = new ReviewModel();
        $idlist=$this->request->param("idlist");
        $n = $summitBanner->where("id","in",$idlist)->update(['status' => 1]);
        return json($n !==false ? ["code" => 1,"msg" => "所选记录已下线"] : ["code" => 2,"msg" => "操作失败，请重试"]);
    }

}