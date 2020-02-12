<?php
/**
 * Created by PhpStorm.
 * User: Uroaming
 * Date: 2019/12/25
 * Time: 19:14
 */

namespace app\backstage\controller;
use app\common\controller\Base;
use app\backstage\model\Category as SummitModel;
class Summit extends Base
{
    //会议列表
    public function items()
    {
        $summit_id = $this->request->param('summit_id');
        $start_date = $this->request->param('sd');
        $end_date = $this->request->param('ed');
        $this->assign('summit_id', $summit_id);
        $this->assign('sd', $start_date);
        $this->assign('ed', $end_date);
        $where = [];
        if (!empty($summit_id)) {
            $where['name|summit_id'] = ['like','%'.$summit_id.'%'];
        }
        if (!empty($start_date) && !empty($end_date)) {
            if ($start_date > $end_date) {
                $this->error('开始时间不能大于结束时间');
            }
            $where['start_time'] = ['between', $start_date.','.$end_date];
        } else if (!empty($start_date) && empty($end_date)) {
            $where['start_time'] = ['>', $start_date];
        } else if (empty($start_date) && !empty($end_date)) {
            $where['start_time'] = ['<', $end_date];
        }
        $list = SummitModel::where($where)->paginate(20)->each(function ($item){
            if (empty($item->img)) {
                $item->img = '/static/backend/images/ico-pic.png';
            }

            if ($item->start_time > date('Y-m-d')) {
                $item->summit_status_str = '未开始';
                $item->summit_status = '1';
            } else if ($item->start_time <= date('Y-m-d') && $item->end_time > date('Y-m-d')) {
                $item->summit_status_str = '进行中';
                $item->summit_status = '2';
            } else {
                $item->summit_status_str = '已结束';
                $item->summit_status = '3';
            }
        });

        $this->assign("items",$list);
        return $this->fetch();
    }

    //添加会议
    public function add()
    {
        if ($this->request->isPost()){
            $postData = $this->request->post();
            if (empty($postData['name'])) {
                return json(['code' => 0, 'msg' => '请填写会议名称']);
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
                return json(['code' => 0, 'msg' => '请填写会议地址']);
            }
            if (empty($postData['number'])) {
                return json(['code' => 0, 'msg' => '请填写会议规模']);
            }
            if (!is_numeric($postData['number'])) {
                return json(['code' => 0, 'msg' => '会议规模请填写整数']);
            }

            if (empty($postData['id'])) {
                //生成会议id编号
                $postData['summit_id'] = 'HY' . date('YmdHis') . rand('100', '200');
                //新增
                $res = SummitModel::create($postData,true);
            } else {
                //编辑
                $res = SummitModel::update($postData,['id'=>$postData['id']],true);
            }

            if ($res !== false) {
                return json(['code'=>1,'msg'=>'保存成功！']);
            } else {
                return json(['code' => 0, 'msg' => '操作失败，请重试']);
            }
        } else {
            $id = $this->request->param("id");
            if ($id != null) {
                $model = SummitModel::get($id);
                $this->assign("model",$model);
            }
            return $this->fetch();
        }
    }

    //删除一个会议
    public function delete()
    {
        $id = $this->request->param('id');
        $res = SummitModel::where(['id' => $id])->update(['delete_time' =>1]);
        if ($res === false) {
            return json(["code" => 2, "msg" => "删除失败，请重试！"]);
        }

        return json(["code" => 1, "msg" => "删除成功"]);
    }




}