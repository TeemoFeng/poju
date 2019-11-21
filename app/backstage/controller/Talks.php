<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-08-26
 * Time: 16:54
 */

namespace app\backstage\controller;
use app\common\controller\Base;
use app\backstage\model\OriginatorUser;
use app\backstage\model\Originator;
use app\backstage\model\Category;
use app\backstage\model\OriginatorTime;
use think\Db;
use think\Request;
class Talks extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->db_app = Db::connect('database_morketing');

    }
    public function items()
    {
        $originatorUser = new OriginatorUser();
        $originator = new Originator();
        $originatorTimeModel = new OriginatorTime();
        $condition = [];
        $name = $this->request->param('name');
        if (!empty($name)) {
            $condition['name']= ['like','%'.$name.'%'];
            $this->assign('name', $name);
        }

        $list = $originatorUser->where($condition)->order('id', 'asc')->paginate(20)->each(function ($item) use($originatorTimeModel, $originator) {
            $time_list = $originatorTimeModel->where(['user_id' => $item->user_id])->select()->toArray();
            //自己设置的应约时间
            $originator_time_list = [];
            if (!empty($time_list)) {
                foreach ($time_list as $k => $v) {
                    $start_time = str_replace('-', '.', $v['start_time']);
                    $end_time = str_replace('-', '.', $v['end_time']);
                    $originator_time_list[] = substr($start_time, 0, 16) . ' - ' . substr($end_time, 11, 5);
                }
            }

            $originator_list = $originator->where(['form_user' => $item->user_id])->select()->toArray();
            //用户已发出预约时间
            $do_originator_list = [];
            if (!empty($originator_list)) {
                foreach ($originator_list as $kk => $vv) {
                    $start_time = str_replace('-', '.', $vv['start_time']);
                    $end_time = str_replace('-', '.', $vv['end_time']);
                    $do_originator_list[] = substr($start_time, 0, 16) . ' - ' . substr($end_time, 11, 5);
                }
            }
            $status = Db::name('booking_room')->where(['guest_id' => $item->user_id])->value('status');
            if ($status == 1) {
                $item->status = 1;
            } else {
                //不存在记录或者status等于2都是退出
                $item->status = 2;
            }
            $item->originator_time_list = $originator_time_list;
            $item->do_originator_list = $do_originator_list;

        });

        $this->assign("page", $list);
        return $this->fetch();
    }

    //添加一个嘉宾
    public function add()
    {
        $this->request->filter('');
        if ($this->request->isPost()){
            $postData = $this->request->post();
            if (empty($postData['name'])) {
                return json(['code' => 0, 'msg' => '请填写嘉宾姓名']);
            }
            if (empty($postData['company'])) {
                return json(['code' => 0, 'msg' => '请填写企业名称']);
            }
            if (empty($postData['position'])) {
                return json(['code' => 0, 'msg' => '请填写职务名称']);
            }
            if (empty($postData['mobile'])) {
                return json(['code' => 0, 'msg' => '请填写手机号']);
            }

            if (empty($postData['id'])) {
                //查询originator中是否已存在该用户
                $is_exist = OriginatorUser::where(['mobile' => $postData['mobile']])->find();
                if (!empty($is_exist)) {
                    return json(['code' => 0, 'msg' => '该手机号嘉宾已存在']);
                }
                //查询morketing中是否已存在该用户信息
                $user_info = $this->db_app->table('user')->where(['mobile' => $postData['mobile']])->find();
                //根据手机号或者姓名从嘉宾表中查找用户头像
                $info = Db::table('guest')
                    ->where('name','like', $postData['name'].'%')
                    ->find();
                if (empty($info)) {
                    $avatar =  "/static/backend/images/avatar.png";
                } else {
                    $avatar = $info['avatar'];
                }

                if (!empty($user_info)) {
                    $update['avatar'] = $avatar;
                    $update['is_guest'] = 2;
                    $update['name'] = $postData['name'] ?: '';
                    $update['nickname'] = $postData['name'] ?: '';
                    $update['company'] = $postData['company'] ?: '';
                    $update['position'] = $postData['position'] ?: '';
                    Db::startTrans();
                    $res = $this->db_app->table('user')->where(['id' => $user_info['id']])->update($update);
                    if ($res === false) {
                        Db::rollback();
                        return json(['code' => 0, 'msg' => '操作失败，请重试']);
                    }

                    $postData['user_id'] = $user_info['id'];
                    $res = OriginatorUser::create($postData,true);
                    //清除用户token
                    $this->db_app->table('user_token')->where(['user_id' => $user_info['id']])->update(['expiretime' => time()]);
                    if ($res !== false) {
                        Db::commit();
                        return json(['code'=>1,'msg'=>'保存成功！']);
                    } else {
                        Db::rollback();
                        return json(['code' => 0, 'msg' => '操作失败，请重试']);
                    }

                } else {
                    //先在morketing中创建一个用户
                    $userData = $postData;
                    $userData['password'] = generateMD5WithSalt('123456');
                    $userData['avatar'] = $avatar;
                    $userData['nickname'] = $postData['name'];
                    $userData['create_time'] = time();
                    $userData['update_time'] = time();
                    $userData['is_guest'] = 2;
                    Db::startTrans();
                    $user_id = $this->db_app->table('user')->insertGetId($userData);
                    if ($user_id === false) {
                        Db::rollback();
                        return json(['code' => 0, 'msg' => '操作失败，请重试']);
                    }
                    $postData['user_id'] = $user_id;
                    $res = OriginatorUser::create($postData,true);
                    if ($res !== false) {
                        Db::commit();
                        return json(['code'=>1,'msg'=>'保存成功！']);
                    } else {
                        Db::rollback();
                        return json(['code' => 0, 'msg' => '操作失败，请重试']);
                    }
                }


            } else {
                //编辑
                //获取用户信息
                $user_id = OriginatorUser::where(['id' => $postData['id']])->value('user_id');
                $data = $postData;
                unset($data['id']);
                Db::startTrans();
                $data['is_guest'] = 2;
                $res = $this->db_app->table('user')->where(['id' => $user_id])->update($data);
                $res2 = OriginatorUser::update($postData,['id'=>$postData['id']],true);
                //清除用户token
                $this->db_app->table('user_token')->where(['user_id' => $user_id])->update(['expiretime' => time()]);
                if ($res === false || $res2 === false ) {
                    Db::rollback();
                    return json(['code' => 0, 'msg' => '操作失败，请重试']);
                }
                Db::commit();
                return json(['code'=>1,'msg'=>'保存成功！']);

            }

        } else {
            $id = $this->request->param("id");
            if ($id != null) {
                $model = OriginatorUser::get($id);
                $this->assign("model",$model);
            }
            return $this->fetch();
        }
    }

    //设置洽谈时间段
    public function addTime()
    {
        $this->request->filter('');
        if ($this->request->isPost()){
            $postData = $this->request->post();
            if (empty($postData['user_id'])) {
                return json(['code' => 0, 'msg' => '请选择要添加的嘉宾']);
            }
            if (empty($postData['start_time'])) {
                return json(['code' => 0, 'msg' => '请选择开始时间']);
            }
            if (empty($postData['end_time'])) {
                return json(['code' => 0, 'msg' => '请选择结束时间']);
            }

            if (strtotime($postData['start_time']) > strtotime($postData['end_time'])) {
                return json(['code' => 0, 'msg' => '开始时间要小于结束时间']);
            }
            //新增
            $res = OriginatorTime::create($postData,true);
            if ($res !== false) {
                return json(['code'=>1,'msg'=>'保存成功！']);
            } else {
                return json(['code' => 0, 'msg' => '操作失败，请重试']);
            }
        } else {
            $id = $this->request->param("id"); //获取洽谈人员id
            if ($id != null) {
                $originatorTimeModel = new OriginatorTime();
                $model = $originatorTimeModel->where(['user_id' => $id])->order('id', 'asc')->select()->toArray(); //获取洽谈人员时间设置列表
                $this->assign("list",$model);
            }
            $this->assign('user_id', $id);
            return $this->fetch();
        }
    }

    //删除一个洽谈用户
    public function delete()
    {
        $id = $this->request->param("id");
        $model = new OriginatorUser();
        $user_id = $model->where(['id' => $id])->value('user_id');
        $this->db_app->table('user')->where(['id' => $user_id])->update(['is_guest' => 1]);
        $res = $model->where("id", "=" , $id)->delete();
        if ($res === false) {
            return json(["code" => 0,"msg" => "删除失败"]);
        }
        return json(["code" => 1,"msg" => "该条记录已删除"]);
    }

    //删除用户设置的时间段
    public function deleteTime()
    {

        $id = $this->request->param("id");
        $is_have = Originator::where(['time_id' => $id, 'status' => ['in', [0,1]]])->select()->toArray();
        if (!empty($is_have)) {
            return json(["code" => 0,"msg" => "时间段已被预约，暂时不可删除"]);
        }
        $n = OriginatorTime::where("id","=", $id)->delete();
        if ($n === false) {
            return json(["code" => 0,"msg" => "删除失败"]);
        }
        return json(["code" => 1,"msg" => "该条记录已删除"]);
    }

    //查看时间段
    public function check()
    {
        $user_id = $this->request->param("id"); //获取洽谈人员id
        $originatorTimeModel = new OriginatorTime();
        $model = $originatorTimeModel->where(['user_id' => $user_id])->order('id', 'asc')->select()->toArray(); //获取洽谈人员时间设置列表
        $this->assign("list", $model);
        return $this->fetch('check2');

    }

    //修改时间段
    public function edit()
    {
        if ($this->request->isPost()) {
            $postData = $this->request->post();
            $id = $postData['id'];
            $is_have = Originator::where(['time_id' => $id, 'status' => ['in', [0,1]]])->select()->toArray();
            if (!empty($is_have)) {
                return json(["code" => 0,"msg" => "时间段已被预约，暂时不可修改"]);
            }

            $save['start_time'] = $postData['start_time' . $id];
            $save['end_time'] = $postData['end_time' . $id];

            $res = OriginatorTime::update($save,['id'=>$id],true);
            if ($res !== false) {
                return json(['code'=>1,'msg'=>'保存成功！']);
            } else {
                return json(['code' => 0, 'msg' => '操作失败，请重试']);
            }

        } else {
            return json(["code" => 0,"msg" => "请求出错"]);
        }


    }

    //进入预约室
    public function enter()
    {
        $user_id = $this->request->param("id");
        //获取用户信息
        $info = $this->db_app->table('user')->where(['id' => $user_id])->field('id,avatar,company,position,name,is_guest')->find();
        $is_have = Db::name('booking_room')->where(['guest_id' => $user_id])->find();
        if (!empty($is_have)) {
            if ($is_have['status'] == 2) {
                Db::name('booking_room')->where(['guest_id' => $user_id])->update(['status' => 1]);
            }
            return json(["code" => 1,"msg" => "进入成功"]);
        } else {
            //获取会议id
            $where['state'] = 1; //开放中的
            $summit_info = Category::where($where)->field('id summit_id,name,img,start_time,end_time')->order('sort', 'asc')->find()->toArray(); //获取排序最靠前的一个会议（这个系统默认主办一个会议）

            $str = json_encode($info);
            $data = [
                'guest_id' => $user_id,
                'summit_id' => $summit_info['summit_id'],
                'guest_info' => $str,
                'status' => 1,
            ];
            $res = Db::name('booking_room')->insert($data);
            if ($res === false) {
                return json(["code" => 0,"msg" => "操作失败，请重试"]);
            }
            return json(["code" => 1,"msg" => "进入成功"]);
        }


    }

    //退出预约室
    public function quit()
    {
        $user_id = $this->request->param("id");

        $is_have = Db::name('booking_room')->where(['guest_id' => $user_id])->find();
        if (!empty($is_have)) {
            if ($is_have['status'] == 1) {
                Db::name('booking_room')->where(['guest_id' => $user_id])->update(['status' => 2]);
            }
            return json(["code" => 1,"msg" => "退出成功"]);

        } else {
            return json(["code" => 0,"msg" => "尚未进入，不需要退出"]);
        }

    }

    //批量删除
    public function deleteInBatch()
    {
        $idlist = $this->request->param("idlist");
        $model = new OriginatorUser();
        $user_ids = $model->where('id', 'in', $idlist)->column('user_id');
        $where['id'] = ['in', $user_ids];
        $this->db_app->table('user')->where($where)->update(['is_guest' => 1]);
        $res = $model->where("id", "in" , $idlist)->delete();
        if ($res === false) {
            return json(["code" => 0,"msg" => "删除失败"]);
        }
        return json(["code" => 1,"msg" => "已删除"]);
    }

}