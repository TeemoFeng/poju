<?php
/**
 * Created by PhpStorm.
 * User: Uroaming
 * Date: 2019/10/30
 * Time: 11:49
 */

namespace app\backstage\controller;
use app\backstage\model\OriginatorTime;
use app\common\controller\Base;
use app\backstage\model\OriginatorUser;
use app\backstage\model\Space;
use app\backstage\model\Originator as OriginatorModel;
use think\Db;
use think\Request;
use Tools\Alisms;

class Originator extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->db_app = Db::connect('database_morketing');

    }

    public function items()
    {

        $userModel = new OriginatorUser();
        $addressModel = new Space();
        $condition = [];
        $condition2 = [];
        $condition3 = [];
        $name = $this->request->param('name');
        if (!empty($name)) {
            $condition['name']= ['like','%'.$name.'%'];
            //获取morketing数据库中用户id
            $user_ids = $userModel->where($condition)->column('user_id');
            if (!empty($user_ids)) {
                $condition2['form_user'] = ['in', $user_ids];
                $condition3['to_user'] = ['in', $user_ids];
            }
            $this->assign('name', $name);
        }
        $originatorModel = new OriginatorModel();
        $list = $originatorModel->where($condition2)->whereOr($condition3)->order('create_time','desc')->paginate()
            ->each(function ($item) use ($userModel, $addressModel){
                $form_user = $userModel->where(['user_id' => $item->form_user])->value('name');
                $to_user = $userModel->where(['user_id' => $item->to_user])->value('name');
                $address = $addressModel->where(['id' => $item->space_id])->value('name');
                $item->form_user_str = $form_user ?: '未找到';
                $item->to_user_str = $to_user ?: '未找到';
                $item->address = $address ?: '未分配';
                $start_time = str_replace('-', '.', $item->start_time);
                $end_time = str_replace('-', '.', $item->end_time);
                $item->talk_time = substr($start_time, 0, 16) . ' - ' . substr($end_time, 11, 5);

                if (strtotime($item['end_time']) < time()) {
                    $item->status = 4;
                }
                $item->status_str = \app\backstage\model\Originator::$status[$item->status];

            });
        $this->assign(['items'=>$list]);
        return $this->fetch();
    }

    //修改洽谈时间 [old 弃用]
    public function editTime()
    {
        $originatorModel = new OriginatorModel();
        if ($this->request->isPost()) {
            $postData = $this->request->post();
            if (empty($postData['start_time'])) {
                return json(['code' => 0, 'msg' => '请选择开始时间']);
            }
            if (empty($postData['end_time'])) {
                return json(['code' => 0, 'msg' => '请选择结束时间']);
            }

            if (strtotime($postData['start_time']) > strtotime($postData['end_time'])) {
                return json(['code' => 0, 'msg' => '开始时间要小于结束时间']);
            }
            $data = $postData;
            unset($data['id']);
            $res = $originatorModel::update($data,['id'=>$postData['id']],true);
            if ($res !== false) {
                return json(['code' => 1,'msg' => '修改成功！']);
            } else {
                return json(['code' => 0, 'msg' => '操作失败，请重试']);
            }

        } else {
            $id = $this->request->param("id");
            $model = $originatorModel->where(['id' => $id])->find()->toArray();

            $this->assign("model", $model);
            return $this->fetch('edit_time');
        }

    }

    //修改洽谈时间
    public function editTime2()
    {
        $originatorModel = new OriginatorModel();
        if ($this->request->isPost()) {
            $postData = $this->request->post();
            if (empty($postData['time_id'])) {
                return json(['code' => 0, 'msg' => '请选择要修改的时间段']);
            }
            //查询时间id是否存在
            $time_info = OriginatorTime::where(['id' => $postData['time_id']])->find();
            if (!$time_info) {
                return json(['code' => 0, 'msg' => '该嘉宾已取消该时间段预约']);
            }

            //判断所选时间段是否已过期
            if (strtotime($time_info['end_time']) < time()) {
                return json(['code' => 0, 'msg' => '该时间段已过期，请重新选择']);
            }

            //查询原先洽谈信息
            $originator_info = $originatorModel->where(['id' => $postData['id']])->find()->toArray();
            //判断当前预约状态
            if (!empty($originator_info) && $originator_info['status'] == 0) {
                $type = 1;
            }
            if (!empty($originator_info) && $originator_info['status'] == 1) {
                $type = 2;
            }

            $update = [
                'start_time' => $time_info['start_time'],
                'end_time' => $time_info['end_time'],
                'time_id' => $postData['time_id'],
            ];
            $res = $originatorModel->where(['id' => $postData['id']])->update($update);
            if ($res === false) {
                return json(['code' => 0, 'msg' => '修改失败']);
            }

            //发送短信通知
            $userModel = new OriginatorUser();
            $form_user = $userModel->where(['user_id' => $originator_info['form_user']])->find()->toArray();
            $to_user = $userModel->where(['user_id' => $originator_info['to_user']])->find()->toArray();

            if ($type == 1 && !empty($form_user) && !empty($to_user)) {
                $param = [];
                $start_time = str_replace('-', '.', $time_info['start_time']);
                $end_time = str_replace('-', '.', $time_info['end_time']);
                $param['time1'] = substr($start_time, 0, 16);
                $param['time2'] = substr($end_time, 11, 5);
                $param['to_user'] = $to_user['company'] . $to_user['position'] . ' ' .$to_user['name'];
                $param['form_user'] = $form_user['company'] . $form_user['position'] . ' ' .$form_user['name'];
                $this->aliSmsSend(1, $param, $form_user['mobile']);
                //发送给应约方
                $this->aliSmsSend(2,$param, $to_user['mobile']);

            }

            if ($type == 2 && !empty($form_user) && !empty($to_user)) {
                $space_name = Space::where(['id' => $originator_info['space_id']])->value('name');
                if (empty($space_name)) {
                    $space_name = '预约场地';
                }
                $param = [];
                $start_time = str_replace('-', '.', $time_info['start_time']);
                $end_time = str_replace('-', '.', $time_info['end_time']);
                $param['time1'] = substr($start_time, 0, 16);
                $param['time2'] = substr($end_time, 11, 5);
                $param['to_user'] = $to_user['company'] . $to_user['position'] . ' ' .$to_user['name'];
                $param['form_user'] = $form_user['company'] . $form_user['position'] . ' ' .$form_user['name'];
                $param['space_name'] = $space_name;
                $this->aliSmsSend(3, $param, $form_user['mobile']);
                //发送给应约方
                $this->aliSmsSend(4,$param, $to_user['mobile']);
            }

            return json(['code' => 1,'msg' => '修改成功！']);

        } else {
            $id = $this->request->param("id");
            $model = $originatorModel->where(['id' => $id])->find();

            $this->assign("model", $model);
            return $this->fetch('edit_time2');
        }

    }

    public function getGuestTimes()
    {
        $id = $this->request->param("user_id");
        //获取应约人的时间段列表
        $model =  OriginatorTime::where(['user_id' => $id])->select()->toArray();
        $new_list = [];
        foreach ($model as $k => $v) {
            if (strtotime($v['end_time']) < time()) {
                continue;
            }
            $start_time = str_replace('-', '.', $v['start_time']);
            $end_time = str_replace('-', '.', $v['end_time']);
            $new_list[$k]['id'] = $v['id'];
            $new_list[$k]['name'] = substr($start_time, 0, 16) . ' - ' . substr($end_time, 11, 5);
        }
        $new_list = array_values($new_list);

        return $new_list;
    }


    //取消洽谈
    public function cancel()
    {
        $id = $this->request->param("id");
        $originatorModel = new OriginatorModel();
        $save['status'] = 3;
        //查询原先洽谈信息
        $originator_info = $originatorModel->where(['id' => $id])->find()->toArray();
        //判断当前预约状态
        if (!empty($originator_info) && $originator_info['status'] == 0) {
            $type = 1;
        }
        if (!empty($originator_info) && $originator_info['status'] == 1) {
            $type = 2;
        }
        $res = OriginatorModel::update($save,['id' => $id],true);
        if ($res !== false) {
            //发送短信通知
            $userModel = new OriginatorUser();
            $form_user = $userModel->where(['user_id' => $originator_info['form_user']])->find()->toArray();
            $to_user = $userModel->where(['user_id' => $originator_info['to_user']])->find()->toArray();
            if ($type == 1 && !empty($form_user) && !empty($to_user)) {
                $param = [];
                $start_time = str_replace('-', '.', $originator_info['start_time']);
                $end_time = str_replace('-', '.', $originator_info['end_time']);
                $param['time1'] = substr($start_time, 0, 16);
                $param['time2'] = substr($end_time, 11, 5);
                $param['to_user'] = $to_user['company'] . $to_user['position'] . ' ' .$to_user['name'];
                $param['form_user'] = $form_user['company'] . $form_user['position'] . ' ' .$form_user['name'];
                $this->aliSmsSend(5, $param, $form_user['mobile']);
                //发送给应约方
                $this->aliSmsSend(6,$param, $to_user['mobile']);

            }

            if ($type == 2 && !empty($form_user) && !empty($to_user)) {
                $param = [];
                $start_time = str_replace('-', '.', $originator_info['start_time']);
                $end_time = str_replace('-', '.', $originator_info['end_time']);
                $param['time1'] = substr($start_time, 0, 16);
                $param['time2'] = substr($end_time, 11, 5);
                $param['to_user'] = $to_user['company'] . $to_user['position'] . ' ' .$to_user['name'];
                $param['form_user'] = $form_user['company'] . $form_user['position'] . ' ' .$form_user['name'];
                $this->aliSmsSend(7, $param, $form_user['mobile']);
                //发送给应约方
                $this->aliSmsSend(8,$param, $to_user['mobile']);
            }

            return json(['code' => 1,'msg' =>'修改成功！']);
        } else {
            return json(['code' => 0, 'msg' => '操作失败，请重试']);
        }
    }

    //删除
    public function delete()
    {
        $id = $this->request->param("id");
        $n = OriginatorModel::where("id","=", $id)->delete();
        if ($n === false) {
            return json(["code" => 0,"msg" => "删除失败"]);
        }
        return json(["code" => 1,"msg" => "该条记录已删除"]);
    }

    //批量删除
    public function deleteInBatch()
    {
        $idlist = $this->request->param("idlist");
        $n = OriginatorModel::where("id","in",$idlist)->delete();
        if ($n === false) {
            return json(["code" => 0,"msg" => "删除失败"]);
        }
        return json(["code" => 1,"msg" => "所选记录已删除"]);
    }


    //阿里云发送各类短信
    public function aliSmsSend($type, $param, $mobile)
    {
        $alisms = new Alisms();
        switch ($type) {
            //管理员修改双方处于“等待应约中”状态下的洽谈时间-发送给预约方
            case 1:
                if (empty($param['to_user']) || empty($param['time1']) || empty($param['time2'])) {
                    return false;
                }
                $alisms->param(['user_info' => $param['to_user'], 'time1' => $param['time1'], 'time2' => $param['time2']]);
                $alisms->template('SMS_177547413');
                $alisms->mobile($mobile);
                $alisms->send();
                break;
            //管理员修改双方处于“等待应约中”状态下的洽谈时间-发送给应约方
            case 2:
                if (empty($param['form_user']) || empty($param['time1']) || empty($param['time2'])) {
                    return false;
                }
                $alisms->param(['user_info' => $param['form_user'], 'time1' => $param['time1'], 'time2' => $param['time2']]);
                $alisms->template('SMS_177537539');
                $alisms->mobile($mobile);
                $alisms->send();
                break;
            //管理员修改双方处于“接受”状态下的洽谈时间-发送给预约方
            case 3:
                if (empty($param['to_user']) || empty($param['time1']) || empty($param['time2'])) {
                    return false;
                }
                $alisms->param(['time1' => $param['time1'], 'time2' => $param['time2'], 'user_info' => $param['to_user'], 'space_name' => $param['space_name']]);
                $alisms->template('SMS_177547518');
                $alisms->mobile($mobile);
                $alisms->send();
                break;
            //管理员修改双方处于“接受”状态下的洽谈时间-发送给应约方
            case 4:
                if (empty($param['form_user']) || empty($param['time1']) || empty($param['time2'])) {
                    return false;
                }
                $alisms->param(['time1' => $param['time1'], 'time2' => $param['time2'],'user_info' => $param['form_user'], 'space_name' => $param['space_name']]);
                $alisms->template('SMS_177542524');
                $alisms->mobile($mobile);
                $alisms->send();
                break;
            //管理员取消双方处于“等待应约中”状态下的洽谈时间——发送给预约方
            case 5:
                if (empty($param['to_user']) || empty($param['time1']) || empty($param['time2'])) {
                    return false;
                }
                $alisms->param(['user_info' => $param['to_user'], 'time1' => $param['time1'], 'time2' => $param['time2']]);
                $alisms->template('SMS_177552480');
                $alisms->mobile($mobile);
                $alisms->send();
                break;
            //管理员取消双方处于“等待应约中”状态下的洽谈时间——发送给应约方
            case 6:
                if (empty($param['form_user']) || empty($param['time1']) || empty($param['time2'])) {
                    return false;
                }
                $alisms->param(['user_info' => $param['form_user'], 'time1' => $param['time1'], 'time2' => $param['time2']]);
                $alisms->template('SMS_177552481');
                $alisms->mobile($mobile);
                $alisms->send();
                break;
            //管理员取消双方处于“接受”状态下的洽谈时间——发送给预约方
            case 7:
                if (empty($param['to_user']) || empty($param['time1']) || empty($param['time2'])) {
                    return false;
                }
                $alisms->param(['user_info' => $param['to_user'], 'time1' => $param['time1'], 'time2' => $param['time2']]);
                $alisms->template('SMS_177542541');
                $alisms->mobile($mobile);
                $alisms->send();
                break;
            //管理员取消双方处于“接受”状态下的洽谈时间——发送给应约方
            case 8:
                if (empty($param['form_user']) || empty($param['time1']) || empty($param['time2'])) {
                    return false;
                }
                $alisms->param(['user_info' => $param['form_user'], 'time1' => $param['time1'], 'time2' => $param['time2']]);
                $alisms->template('SMS_177542545');
                $alisms->mobile($mobile);
                $alisms->send();
                break;


        }


    }

}