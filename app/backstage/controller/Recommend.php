<?php
/**
 * Created by PhpStorm.
 * User: Uroaming
 * Date: 2020/1/10
 * Time: 9:26
 */

namespace app\backstage\controller;
use app\common\controller\Base;
use app\backstage\model\Recommend as RecommendModel;
class Recommend extends Base
{
    //近期推荐列表
    public function items()
    {

        $list = RecommendModel::order('sort ASC')->paginate(20);

        $this->assign("items", $list);
        $this->assign("type", RecommendModel::$types);
        $this->assign('status_str', RecommendModel::$status);
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
            if (empty($postData['start_time'])) {
                return json(['code' => 0, 'msg' => '请选择开始时间']);
            }
            if (empty($postData['end_time'])) {
                return json(['code' => 0, 'msg' => '请选择结束时间']);
            }
            if ($postData['start_time'] > $postData['end_time']) {
                return json(['code' => 0, 'msg' => '开始时间不能大于结束时间']);
            }

            if (empty($postData['address'])) {
                return json(['code' => 0, 'msg' => '请填写地点']);
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
            if(isset($postData['is_show'])){
                $postData['is_show'] = 1;
            }else{
                $postData['is_show'] = 0;
            }
            if (empty($postData['id'])) {
                //新增
                $res = RecommendModel::create($postData,true);
            } else {
                //编辑
                $res = RecommendModel::update($postData,['id'=>$postData['id']],true);
            }

            if ($res !== false) {
                return json(['code'=>1,'msg'=>'保存成功！']);
            } else {
                return json(['code' => 0, 'msg' => '操作失败，请重试']);
            }
        } else {
            $id = $this->request->param("id");
            if ($id != null) {
                $model = RecommendModel::get($id);
                $this->assign("model",$model);
            }
            return $this->fetch();
        }
    }

    //获取类别列表
    public function getTypeList()
    {
        $list = [
            [
                'id' => '1',
                'name' => '普通轮播广告位',
            ],
            [
                'id' => '2',
                'name' => '固定强推广告位',
            ],
        ];
        return $list;

    }

    //获取标签列表
    public function getTagList()
    {
        $list = \app\backstage\model\Recommend::$list;
        return $list;

    }

    //删除一个近期推荐
    public function delete()
    {
        $id = $this->request->param('id');
        $res = RecommendModel::where(['id' => $id])->delete();
        if ($res === false) {
            return json(["code" => 2, "msg" => "删除失败，请重试！"]);
        }

        return json(["code" => 1, "msg" => "删除成功"]);
    }

    //批量删除
    public function deleteInBatch()
    {
        $summitBanner = new RecommendModel();
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
        $summitBanner = new RecommendModel();
        $n = $summitBanner->saveAll($list);
        return json( count($n)>0?["code" => 1, "msg" => "保存成功！"]:["code" => 2, "msg" => "保存失败！"]);
    }

    public function status()
    {
        $id = $this->request->param('id');
        $status = $this->request->param('status');
        $res = RecommendModel::where(['id' => $id])->update(['status' => $status]);
        if ($res === false) {
            return json(["code" => 2, "msg" => "操作失败"]);
        }

        return json(["code" => 1, "msg" => "操作成功"]);
    }

    //批量上线所选记录
    public function onlineInBatch()
    {
        $summitBanner = new RecommendModel();
        $idlist=$this->request->param("idlist");
        $n = $summitBanner->where("id","in",$idlist)->update(['status' => 2]);
        return json($n !==false ? ["code" => 1,"msg" => "所选记录已上线"] : ["code" => 2,"msg" => "操作失败，请重试"]);
    }

    //批量下线所选记录
    public function offlineInBatch()
    {
        $summitBanner = new RecommendModel();
        $idlist=$this->request->param("idlist");
        $n = $summitBanner->where("id","in",$idlist)->update(['status' => 1]);
        return json($n !==false ? ["code" => 1,"msg" => "所选记录已下线"] : ["code" => 2,"msg" => "操作失败，请重试"]);
    }

}