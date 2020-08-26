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
use think\Session;

class Index extends WebBase
{
    public function index()
    {

        $sid = $this->request->param('summit');
        $user_id = $this->request->param('u');
        if (!empty($user_id)) {
            //获取用户信息
            $this->db_app = Db::connect('database_morketing');
            $user_info = $this->db_app->table('user')->where(['id' => $user_id])->find();
            if (!empty($user_info)) {
                Session::set('userInfo', $user_info);
            }

        }

        if(empty($sid)){
            $infoModel = $this->category->where(['state' =>1])->order('sort asc')->find();
        }else{
            $infoModel = $this->category->where(['realm_name' => $sid])->find();
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
        //区号列表
        $this->db_app = Db::connect('database_morketing');
        $country_mobile_prefix = $this->db_app->name('country_mobile_prefix')->order('id ASC')->column('mobile_prefix','id');
        $this->assign('country_mobile_prefix', $country_mobile_prefix);
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
        $user_info = Session::get('userInfo');
        if (empty($user_info)) {
            return json(['code'=>3,'msg'=>'您尚未登录，请先登录！']);
        }
        $model = $this->category->where(['id' => $id])->find();
        if(empty($model)){
            return json(['code'=>2,'msg'=>'请求出错了！']);
        }
        return json($this->createForm($model['diy_form'], $id, $user_info));

    }

    private function createForm($str = null, $cid, $user_info)
    {
        //获取官网设置的form表单
        $this->db_app = Db::connect('database_morketing');

        $idList = explode(',',$str);
        $formStrHead = '<div class="financing-modal upload-financing-modal" id="report-modal"><div class="modal-dialog"><div class="modal-content"><div class="f-table">'.
            '<form action="'.url('index/index/subInfo').'" ajax="true" novalidate="novalidate"  method="post" success="msg.tips">'.
            '<input type="hidden" name="cid" value="'.$cid.'"><input type="hidden" name="user_id" value="'.$user_info['id'].'"><ul>';
        $formStrEnd = '<li><div class="f-name"></div><div class="f-con clearfix"><div class="f-btns"><button class="set-btn">提交</button></div></div></li></ul></form></div></div></div></div>';
        foreach ($idList as $item){
            $elemItem = $this->db_app->table('diy_form')->where(['id' => $item])->find();
            $validate = explode(',',$elemItem['validate']);
            $v = '';
            if (!empty($elemItem)) {
                if (isset($user_info[$elemItem['name']]) && !empty($user_info[$elemItem['name']])) {
                    $v .= ' value="'.$user_info[$elemItem['name']].'"';
                }
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
                        foreach ($option as $k => $oItem){
                            $opt.='<label><input class="f-input f-input-auto" name="'.$elemItem['name'].'" type="radio" value="'.$k.'"> '. $oItem.'</label>';
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
        return ['code' => 1, 'data' => $formStrHead.$formStrEnd];

    }

    public function subInfo()
    {
        $postData = $this->request->post();
        //查看该用户是否已经报名
        $is_post = Db::name('summit_enroll')->where(['user_id' => $postData['user_id'], 'cid' => $postData['cid']])->find();
        if (!empty($is_post)) {
            return json(['code'=> 0,'msg'=>'该会议您已报名']);
        }
        $this->db_app = Db::connect('database_morketing');
        //获取会议id
        $cid = $postData['cid'];
        if (empty($cid)) {
            return json(['code'=> 0,'msg'=>'出现未知错误']);
        }
        $diy_form = $this->category->where(['id' => $cid])->value('diy_form');
        $idList = explode(',',$diy_form);
        $this->db_app = Db::connect('database_morketing');
        $user_info = $this->db_app->table('user')->where(['id' => $postData['user_id']])->find();
        $user_update = []; //完善用户信息
        foreach ($idList as $item){
            $elemItem = $this->db_app->table('diy_form')->where(['id' => $item])->find();
            $validate = explode(',',$elemItem['validate']);
            if (isset($user_info[$elemItem['name']]) && empty($user_info[$elemItem['name']])) {
                $user_update[$elemItem['name']] = $postData[$elemItem['name']];
            }
            if (!empty($elemItem)) {
                foreach ($validate as $vItem){
                    $error = 0;
                    switch ($vItem){
                        case 'required':
                            if (empty($postData[$elemItem['name']])) {
                                $error = 1;
                                $msg = $elemItem['label'] . '不能为空';
                            }
                            break;
                        case 'email':
                            $result  = filter_var($postData[$elemItem['name']], FILTER_VALIDATE_EMAIL);
                            if ($result === false) {
                                $error = 1;
                                $msg = '请填写正确的' . $elemItem['label'];
                            }
                            break;
                        case 'mobile':
                            if(!preg_match("/^1[345678]{1}\d{9}$/",$postData['mobile'])) {
                                $error = 1;
                                $msg = '请填写正确的' . $elemItem['label'];
                            }
                            break;
                    }
                    if ($error == 1) {
                        return json(['code'=> 0,'msg'=> $msg]);
                    }
                }


            }

        }

        if (!empty($postData['name'])) {
            $res = preg_match('/\d+/', $postData['name']);
            if ($res) {
                return json(['code'=> 0,'msg'=>'姓名格式不正确']);
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

        $postData['sub_time'] = time();
        $res = Db::name('summit_enroll')->insert($postData);
        if (!empty($user_update)) {
            $this->db_app->table('user')->where(['id' => $postData['user_id']])->update($user_update);
        }

        if ($res === false) {
            return json(['code'=> 0,'msg'=>'报名失败请重试']);
        }
        return json(['code'=>1,'msg'=>'报名成功']);
    }

}
