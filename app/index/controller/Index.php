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
use think\Db;

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
        $adsList = $ads->where('tid','=',$infoModel['id'])->order('displayorder','asc')->select();
        //合作伙伴
        $cooperative = new Cooperative();
        $cooperList = $cooperative->where('sid','=',$infoModel['id'])->order('sort','asc')->select();
        //视频
        $videos = Video::where('sid','=',$infoModel['id'])->order('sort','asc')->select();
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
        $sid = $this->request->param('sid', 1);
        if(empty($sid)){
            $infoModel = $this->category->getLastOne();
        }else{
            $infoModel = $this->category->find($sid);
        }

        $topic = new Topic();

        $jy = $topic->where('tid = 1')->where('sid = ' . $sid )->find();
        $yy = $topic->where('tid = 2')->where('sid = ' . $sid )->find();
        $tb = $topic->where('tid = 3')->where('sid = ' . $sid )->find();

        //联系方式
        $fragment =  new Fragment();
        $lx = $fragment->where(['sid' => $infoModel['id']])->select();
        $host = request()->root(true);
        $this->assign([
            'model'=>$infoModel,
            'contact'=>$lx,
            'jy'=>$jy,
            'yy'=>$yy,
            'tb'=>$tb,
            'sid' => $sid,
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

    //报名请求返回需要填写的表单
    public function getForm()
    {

        $id = $this->request->param('id');
        $model = $this->category->where(['id' => $id])->find();
        if(empty($model)){
            return json(['code'=>2,'msg'=>'请求出错了！']);
        }
        return $this->createForm($model['diy_form'], $id);

    }

    private function createForm($str = null,$infoId)
    {
        //获取官网设置的form表单
        $this->db_app = Db::connect('database_morketing');
        $elemItem = $this->db_app->table('diy_form')->select();


        $idList = explode(',',$str);
        $formStrHead = '<div class="financing-modal upload-financing-modal" id="report-modal"><div class="modal-dialog"><div class="modal-content"><div class="f-table">'.
            '<form action="'.url('index/index/subInfo').'" ajax="true" novalidate="novalidate" data-valid="true" method="post" success="mk.msg.tips">'.
            '<input type="hidden" name="cid" value="'.$infoId.'"><ul>';
        $formStrEnd = '<li><div class="f-name"></div><div class="f-con clearfix"><div class="f-btns"><button class="set-btn">提交</button></div></div></li></ul></form></div></div></div></div>';
        foreach ($idList as $item){
//            $elemItem = DiyForm::get($item);
            $elemItem = $this->db_app->table('diy_form')->where(['id' => $item])->find();
            $validate = explode(',',$elemItem['validate']);
            $v = '';
            if (!empty($elemItem)) {
                foreach ($validate as $vItem){
                    switch ($vItem){
                        case 'required':
                            $v.=' required required-msg="请填写'.$elemItem['label'].'"';
                            break;
                        case 'email':
                            $v.=' email="true" email-msg="请填写正确的'.$elemItem['label'].'"';
                            break;
                        case 'mobile':
                            $v.=' mobile="true" mobile-msg="请填写正确的'.$elemItem['label'].'"';
                            break;
                    }
                }

                switch ($elemItem['input_type']){
                    case 0:
                        $formStrHead.= '<li><div class="f-name">'.$elemItem['label'].'：</div><div class="f-con"><input class="f-input" name="'.$elemItem['name'].'" type="text" '.$v.' placeholder="请填写'.$elemItem['label'].'"></div></li>';
                        break;
                    case 1:
                        $formStrHead.= '<li><div class="f-name">'.$elemItem['label'].'：</div><div class="f-con"><textarea class="f-input f-ttarea" name="'.$elemItem['name'].'" '.$v.' placeholder="请填写'.$elemItem['label'].'" rows="4"></textarea></div></li>';
                        break;
                    case 2:
                        $option = explode(',',$elemItem['option']);
                        $opt = '';
                        foreach ($option as $oItem){
                            $opt.='<option value="'.$oItem.'">'.$oItem.'</option>';
                        }
                        $formStrHead.='<li><div class="f-name">'.$elemItem['label'].'：</div><div class="f-con"><select class="f-input" name="'.$elemItem['name'].'">'.$opt.'</select></div></li>';
                        break;
                    case 3:
                        $option = explode(',',$elemItem['option']);
                        $opt = '';
                        foreach ($option as $oItem){
                            $opt.='<label><input class="f-input f-input-auto" name="'.$elemItem['name'].'" type="radio"> '. $oItem.'</label>';
                        }
                        $formStrHead.='<li><div class="f-name">'.$elemItem['label'].'：</div><div class="f-con">'.$opt.'</div></li>';
                        break;
                    case 4:
                        $option = explode(',',$elemItem['option']);
                        $opt = '';
                        foreach ($option as $oItem){
                            $opt.='<label>'.$oItem.':<input class="f-input f-input-auto" name="'.$elemItem['name'].'" type="checkbox"></label>';
                        }
                        $formStrHead.='<li><div class="f-name">'.$elemItem['label'].'：</div><div class="f-con">'.$opt.'</div></li>';
                        break;
                }
            }

        }
        return $formStrHead.$formStrEnd;

    }

    public function subInfo()
    {
        // if(empty($this->UserInfo)){
        //     return json(['code'=>2,'msg'=>'未登录！']);
        // }
        $postData = $this->request->post();
        if (!empty($postData['name'])) {
            $res = preg_match('/\d+/', $postData['name']);
            if ($res) {
                return json(['code'=> 0,'msg'=>'姓名格式不正确']);
            }
        }
        if (!empty($postData['mobile'])) {
            if(!preg_match("/^1[345678]{1}\d{9}$/",$postData['mobile'])) {
                return json(['code' => 0, 'msg' => '请填写正确的手机号']);
            }
        }
        if (!empty($postData['email'])) {
            if (!filter_var($postData['email'], FILTER_VALIDATE_EMAIL)) {
                return json(['code' => 0, 'msg' => '请填写正确的邮箱']);
            }
        }

        if (!empty($postData['company'])) {
            $res = preg_match('/\d+/', $postData['company']);
            if ($res) {
                return json(['code'=> 0,'msg'=>'公司名称格式不正确']);
            }
        }
        if (!empty($postData['department'])) {
            $res = preg_match('/\d+/', $postData['department']);
            if ($res) {
                return json(['code'=> 0,'msg'=>'部门格式不正确']);
            }
        }
        if (!empty($postData['position'])) {
            $res = preg_match('/\d+/', $postData['position']);
            if ($res) {
                return json(['code'=> 0,'msg'=>'职位格式不正确']);
            }
        }

        // $postData['uid'] = $this->UserInfo['id'];
        $postData['sub_time'] = time();
        //$isSub = DiyInfo::where(['uid'=>$this->UserInfo['id'],'tid'=>$postData['tid']])->find();
        // if(empty($isSub)){
        DiyInfo::create($postData,true);
        //  }
        cookie($postData['tid'],time(),604800);
        return json(['code'=>1,'msg'=>'提交成功,再次【点击下载】即可下载完整报告']);
    }

}
