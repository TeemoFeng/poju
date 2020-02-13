<?php
namespace app\index\controller;
use app\backstage\model\Ads;
use app\backstage\model\Agenda;
use app\backstage\model\Cooperative;
use app\backstage\model\Feedback;
use app\backstage\model\Fragment;
use app\backstage\model\Guest;
use app\backstage\model\Topic;
use app\backstage\model\Video;
class Index extends WebBase
{
    public function index()
    {
        $sid = $this->request->param('sid');
        if(empty($sid)){
            $infoModel = $this->category->getLastOne();
        }else{
            $infoModel = $this->category->find($sid);
        }

        //共创人/演讲嘉宾
        $guest = new Guest();
        $guestList = $guest->where(['sid'=>0, 'cid' => $infoModel['id']])->order('sort','asc')->paginate(12); //演讲嘉宾
        $builder = $guest->where(['sid'=>1, 'cid' => $infoModel['id']])->order('sort','asc')->paginate(12); //共建人
        //峰会议程
        $agenda = new Agenda();
        $agendaItems = $agenda->where('sid','=',$infoModel['id'])->order('sort','asc')->select();
        //联系方式
        $fragment =  new Fragment();
        $lx = $fragment->where(['sid' => $infoModel['id']])->select();
        //往期回顾
        $ads = new Ads();
        $adsList = $ads->where('tid','=',0)->order('displayorder','asc')->select();
        //合作伙伴
        $cooperative = new Cooperative();
        $cooperList = $cooperative->where('sid','=',$infoModel['id'])->order('sort','asc')->select();


        $videos = Video::where('id','>',0)->order('sort','asc')->select();
        // 调整结构
        $list = [];
        foreach ($videos as $item) {
            if (!isset($list[$item['g_time']])) {
                $list[$item['g_time']] = [];
            }
            array_push($list[$item['g_time']],$item->toArray());
        }

        $this->assign(['model'=>$infoModel,
            'guest'=>$guestList,
            'builder'=>$builder,
            'agenda'=>$agendaItems,
            'contact'=>$lx,
            'ads'=>$adsList,
            'cooper'=>$cooperList,
            'videos'=>$list,
            'sid' => $infoModel['id'],//会议id
        ]);
        return $this->fetch();
    }
    public function getMoreGuest()
    {
        $page = $this->request->param('page');
        $sid = $this->request->param('sid');
        $cid = $this->request->param('cid');
        $guest = new Guest();
        $where['sid'] = $sid;
        $where['cid'] = $cid;
        $guestList = $guest->where($where)->order('sort','asc')->paginate(12,false,['page'=>$page]);
        return json($guestList);
    }
  
  
  
    public function feedback()
    {
        $data = $this->request->post();
        $data['subdate'] = time();
        $data['id'] = 0;
        $fb = new Feedback();
        $res = $fb->allowField(true)->isUpdate(false)->save($data);
        if($res!==false){
            return json(['code'=>1, 'msg'=>'提交成功！']);
        }else{
            return json(['code'=>2, 'msg'=>'服务器繁忙，请稍后重试！']);
        }
    }
    public function prize()
    {
        $sid = $this->request->param('sid');
        if(empty($sid)){
            $infoModel = $this->category->getLastOne();
        }else{
            $infoModel = $this->category->find($sid);
        }

        $topic = new Topic();

        $jy = $topic->where('tid = 1')->find();
        $yy = $topic->where('tid = 2')->find();
        $tb = $topic->where('tid = 3')->find();

        $fragment =  new Fragment();
        $lx = $fragment->select();
        $this->assign([
            'model'=>$infoModel,
            'contact'=>$lx,
            'jy'=>$jy,
            'yy'=>$yy,
            'tb'=>$tb
        ]);
        return $this->fetch();
    }
    public function video()
    {
        $id = $this->request->param('id');
        $model = Video::get($id);
        $this->assign('model',$model);
        return $this->fetch();
    }

}
