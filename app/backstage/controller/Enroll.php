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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class Enroll extends Base
{
    public function items()
    {
        $summit_name = $this->request->param('summit_name', '');
        $sd = $this->request->param('sd');
        $ed = $this->request->param('ed');
        $this->assign('sd', $sd);
        $this->assign('ed', $ed);
        $start_date = !empty($sd) ? strtotime($sd) : 0;
        $end_date = !empty($ed) ? strtotime($ed) : 0;
        $this->assign('summit_name', $summit_name);
        $where2 = [];

        if (!empty($start_date) && !empty($end_date)) {
            if ($start_date > $end_date) {
                $this->error('开始时间不能大于结束时间');
            }
            $where2['sub_time'] = ['between', $start_date.','.$end_date];
        } else if (!empty($start_date) && empty($end_date)) {
            $where2['sub_time'] = ['>', $start_date];
        } else if (empty($start_date) && !empty($end_date)) {
            $where2['sub_time'] = ['<', $end_date];
        }
        $categoryModel = new \app\backstage\model\Category();
        if (!empty($summit_name)) {
            $ids = $categoryModel->where('name',"like","%".$summit_name."%")->column('id');
            if (!empty($ids)) {
                $where2['cid'] = ['in', $ids];
            }
        }
        $list = SummitEnroll::where($where2)->order('id ASC')->paginate(20, false ,['query'=>request()->param()])->each(function ($v) use($categoryModel) {
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


    public function getExcel()
    {
        $chat = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R'];
        $summit_name = $this->request->param('summit_name', '');
        $sd = $this->request->param('sd', 0);
        $ed = $this->request->param('ed', 0);
        $this->assign('sd', $sd);
        $this->assign('ed', $ed);
        $start_date = !empty($sd) ? strtotime($sd) : 0;
        $end_date = !empty($ed) ? strtotime($ed) : 0;
        $this->assign('summit_name', $summit_name);
        $where2 = [];

        if (!empty($start_date) && !empty($end_date)) {
            if ($start_date > $end_date) {
                $this->error('开始时间不能大于结束时间');
            }
            $where2['sub_time'] = ['between', $start_date.','.$end_date];
        } else if (!empty($start_date) && empty($end_date)) {
            $where2['sub_time'] = ['>', $start_date];
        } else if (empty($start_date) && !empty($end_date)) {
            $where2['sub_time'] = ['<', $end_date];
        }
        $categoryModel = new \app\backstage\model\Category();
        if (!empty($summit_name)) {
            $ids = $categoryModel->where('name',"like","%".$summit_name."%")->column('id');
            if (!empty($ids)) {
                $where2['cid'] = ['in', $ids];
            }
        }

        $data = SummitEnroll::where($where2)->order('id ASC')->select();

        $this->db_app = Db::connect('database_morketing');
        $th = $this->db_app->table('diy_form')->select();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("会议报名");
        $startRowIndex = 1;

        foreach($th as $index => $t){
            $sheet->setCellValue($chat[$index].$startRowIndex, $t['label']);
        }
        foreach ($data as $index=>$item){
            $startRowIndex +=1;

            foreach ($th as $key=>$vo){
                $sheet->setCellValue($chat[$key].$startRowIndex, $item[$vo['name']]);
            }

        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.date('Ymd',time()).'会议报名'.'.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}

