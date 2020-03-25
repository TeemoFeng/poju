<?php
/**
 * Created by PhpStorm.
 * User: Uroaming
 * Date: 2020/3/25
 * Time: 16:00
 */
namespace app\backstage\controller;
use app\common\controller\Base;
use app\backstage\model\SummitEnroll;
use think\Config;
use think\Db;

class Enroll extends Base
{
    public function items()
    {
        $categoryModel = new \app\backstage\model\Category();
        $list = SummitEnroll::order('id ASC')->paginate(20)->each(function ($v) use($categoryModel) {
            $v['summit_name'] = $categoryModel::where(['id' => $v['cid']])->value('name');
            $v['sub_time'] = date('Y-m-d H:i:s', $v['sub_time']);
        });
        $this->assign("items", $list);
        return $this->fetch();
    }

    //用户报名提交详情
    public function info()
    {
        $id = $this->request->param('id');
        $model = SummitEnroll::where(['id' => $id])->find();
        $this->db_app = Db::connect('database_morketing');
        $user_info =  $this->db_app->table('user')->where(['id' => $model['user_id']])->find();
        $model['avatar'] = '';
        $model['mk_id'] = $user_info['mk_id'];
        $model['tid'] = $user_info['tid'];
        $model['sub_time'] = date('Y-m-d H:i:s', $model['sub_time']);
        $host = Config::get('morketing_avatar_url');
        if (!empty($user_info['avatar'])) {
            $model['avatar'] = strpos($user_info['avatar'], 'http') !== false ? $user_info['avatar'] : $host . $user_info['avatar'];

        }
        $this->assign('model',$model);
        return $this->fetch();
    }

    //删除
    public function delete()
    {
        $id = $this->request->param('id');
        $res = SummitEnroll::where(['id' => $id])->delete();
        if ($res === false) {
            return json(["code" => 2, "msg" => "删除失败，请重试！"]);
        }

        return json(["code" => 1, "msg" => "删除成功"]);
    }

    //批量删除
    public function deleteInBatch()
    {
        $summitBanner = new SummitEnroll();
        $idlist=$this->request->param("idlist");
        $n = $summitBanner->where("id","in",$idlist)->delete();
        return json($n>0?["code"=>1,"msg"=>"所选记录已删除"]:["code"=>2,"msg"=>"当前没有记录被删除"]);
    }
}

