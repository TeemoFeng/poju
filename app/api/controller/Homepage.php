<?php
/**
 * Created by PhpStorm.
 * User: Uroaming
 * Date: 2020/1/14
 * Time: 9:26
 */

namespace app\api\controller;

use app\api\library\ApiBase;
use app\backstage\controller\Recommend;
use app\backstage\model\Ads;
use app\backstage\model\Agenda;
use app\backstage\model\Category;
use app\backstage\model\Cooperative;
use app\backstage\model\Feedback;
use app\backstage\model\Fragment;
use app\backstage\model\Guest;
use app\backstage\model\RecommendRule;
use app\backstage\model\Report;
use app\backstage\model\Review;
use app\backstage\model\Special;
use app\backstage\model\SummitBanner;
use app\backstage\model\Recommend as RecommendModel;
use app\backstage\model\SysAdmin;
use app\backstage\model\Video;
use think\Config;
use think\Db;
use app\backstage\model\SysConfig as SCModel;

/**
 * 用户
 * Class User
 * @package app\api\controller
 */
class Homepage extends ApiBase {

    /**
     * 无需登录的方法
     */
    protected $noNeedLogin = ['index', 'summit', 'video','videoDetail','latelyVideoList', 'comment', 'addComment', 'collection', 'giveLike', 'recommend', 'copyright', 'recommendViews', 'webLogo', 'bannerAdvert', 'recommendTag', 'bannerViews','userTreaty', 'summitInfo', 'getMoreGuest', 'getForm'];

    /***
     * Action 前台首页
     * @author ywf
     * @license /api/homepage/index POST
     * @para string 无 无|N
     * @field string code   1:成功;0:失败
     * @field string msg    信息提示
     * @field string data.banner_list           banner列表
     * @field string data.recommend_fixed       近期活动强推广告位4个
     * @field string data.recommend_ordinary    近期活动普通广告位10个
     * @field string data.review_list           主办方历届峰会回顾4个
     * @field string data.report_graphic_list   后续报道图文报道4个
     * @field string data.report_video_list     后续报道视频报道4个
     * @jsondata
     * @jsondatainfo {"code":1,"msg":"","time":"1579068680","data":{"banner_list":[{"banner_id":3,"title":"测试会议1","tag":"峰会","img":"/upload/image/2020-01/d46a8c29b2b33b2ae78c4acb89215834.png","jump_url":"www.baidu.com"},{"banner_id":2,"title":"测试会议2","tag":"峰会","img":"/upload/image/2020-01/d46a8c29b2b33b2ae78c4acb89215834.png","jump_url":"www.baidu.com"},{"banner_id":4,"title":"测试会议3","tag":"峰会","img":"/upload/image/2020-01/d46a8c29b2b33b2ae78c4acb89215834.png","jump_url":"www.baidu.com"},{"banner_id":5,"title":"测试会议4","tag":"峰会","img":"/upload/image/2020-01/d46a8c29b2b33b2ae78c4acb89215834.png","jump_url":"www.baidu.com"}],"recommend_fixed":[],"recommend_ordinary":[{"recommend_id":1,"title":"近期推荐1","tag":"峰会","start_time":"2020-01-10","end_time":"2020-01-10","address":"故宫","type":1,"img":"/upload/image/2020-01/d46a8c29b2b33b2ae78c4acb89215834.png","jump_url":"www.baidu.com","views":0},{"recommend_id":2,"title":"近期推荐2","tag":"峰会2","start_time":"2020-01-10","end_time":"2020-01-10","address":"故宫222","type":1,"img":"/upload/image/2020-01/d46a8c29b2b33b2ae78c4acb89215834.png","jump_url":"www.baidu.com","views":0}],"review_list":[{"review_id":1,"title":"峰会回顾1","tag":"峰会","profile":"是发大水发大水","img":"/upload/image/2020-01/d46a8c29b2b33b2ae78c4acb89215834.png","jump_url":"www.baidu.com"},{"review_id":2,"title":"峰会回顾2","tag":"峰会","profile":"是发大水发大水","img":"/upload/image/2020-01/d46a8c29b2b33b2ae78c4acb89215834.png","jump_url":"www.baidu.com"}],"report_graphic_list":[{"report_id":1,"title":"后续报道1","tag":"峰会","profile":"大师傅大师傅的","img":"/upload/image/2020-01/d46a8c29b2b33b2ae78c4acb89215834.png","jump_url":"www.baidu.com"},{"report_id":3,"title":"后续报道2","tag":"峰会","profile":"是发大水发大水","img":"/upload/image/2020-01/d46a8c29b2b33b2ae78c4acb89215834.png","jump_url":"www.baidu.com"}],"report_video_list":[{"report_id":4,"title":"视频报道1","tag":"视频报道","profile":"放松放松的","img":"/upload/image/2020-01/d46a8c29b2b33b2ae78c4acb89215834.png","jump_url":"www.baidu.com"},{"report_id":5,"title":"视频报道2","tag":"视频报道","profile":"是发大水发大水","img":"/upload/image/2020-01/d46a8c29b2b33b2ae78c4acb89215834.png","jump_url":"www.baidu.com"}]}}
     */
    public function index()
    {
        $recommendModel = new RecommendModel();
        $summitModel    = new SummitBanner();
        $reviewModel    = new Review();
        $reportModel    = new Report();
        //获取首页bannner轮播图
        $banner_list = $summitModel->homepageBannerList();
        //近期活动强推广告位3个
        $recommend_fixed = $recommendModel->recommendFixed();
        //近期活动普通广告位
        $recommend_ordinary = $recommendModel->recommendOrdinary();
        //主办方历届峰会回顾
        $review_list = $reviewModel->homepageReviewList();
        //后续报道[图文报道]
        $report_graphic_list =$reportModel->homepageReportList();
        //后续报道[视频报道]
        $report_video_list =$reportModel->homepageReportVideoList();

        $this->success("", ['banner_list' => $banner_list, 'recommend_fixed' => $recommend_fixed, 'recommend_ordinary' => $recommend_ordinary,'review_list' => $review_list, 'report_graphic_list' => $report_graphic_list, 'report_video_list' => $report_video_list]);

    }

    /***
     * Action 近期推荐标签分类
     * @author ywf
     * @license /api/homepage/recommendTag POST
     * @para string tid    返回的标签id
     * @field string code   1:成功;0:失败
     * @field string msg    无提示
     * @field string list  列表
     * @field string list.id  标签id
     * @field string list.name  标签名
     * @field string filter  筛选列表
     * @field string filter.add_tpl
     * @jsondata
     * @jsondatainfo
     */
    public function recommendTag()
    {
        $recommendRuleModel = new RecommendRule();
        $list = $recommendRuleModel->where(['pid' => 22])->select();
        $tid = $this->request->post('tid', $recommendRuleModel->getFirstChildId(22));
        $filterItems = toTree(collection($recommendRuleModel->getChildByPid($tid))->toArray(),'id','pid','subItems',$tid);

        $this->success('', ['list' => $list, 'filter' => $filterItems]);
    }


    /***
     * Action 近期推荐列表页
     * @author ywf
     * @license /api/homepage/recommend POST
     * @para string tag  标签类型：默认1|Y
     * @para string hid  所属行业：默认0|Y
     * @para string gid  活动规模：默认0|Y
     * @para string city  城市：默认0|Y
     * @para string page  页面数,默认1|Y
     * @para string page_size  一页显示条数,默认28|N
     * @field string code   1:成功;0:失败
     * @field string msg    无提示
     * @field string data.count   会议总数
     * @field string data.list    会议列表，为空表示无暂无会议
     * @field string list.recommend_id 推荐id
     * @field string list.title    推荐标题
     * @field string list.tag    标签
     * @field string list.img     会议封面图
     * @field string list.start_time  会议开始时间
     * @field string list.end_time  会议结束时间
     * @field string list.address 地址
     * @field string list.jump_url  跳转会议[查看详情使用]
     * @field string list.views  浏览数
     * @jsondata {"page":"1"}
     * @jsondatainfo {"code":1,"msg":"","time":"1579588908","data":{"count":2,"list":[{"recommend_id":1,"title":"近期推荐1","tag":"峰会","start_time":"2020-01-10","end_time":"2020-01-10","address":"故宫","img":"http:\/\/poju.com\/upload\/image\/2020-01\/d46a8c29b2b33b2ae78c4acb89215834.png","jump_url":"www.baidu.com","views":0},{"recommend_id":2,"title":"近期推荐2","tag":"峰会2","start_time":"2020-01-10","end_time":"2020-01-10","address":"故宫222","img":"http:\/\/poju.com\/upload\/image\/2020-01\/d46a8c29b2b33b2ae78c4acb89215834.png","jump_url":"www.baidu.com","views":0}]}}
     */
    public function recommend()
    {
        $tag = $this->request->post('tag', 42, 'intval'); //活动分类
        $hid = $this->request->post('hid', 0, 'intval');
        $gid = $this->request->post('gid', 0, 'intval');
        $city = $this->request->post('city', 0, 'intval');
        $page = $this->request->post('page', 1, 'intval');
        $page_size = $this->request->post('page_size', 28, 'intval');
        if (!is_numeric($page_size) || $page_size == 0) {
            $page_size = 28;
        }
        $recommendModel = new RecommendModel();
        $where['tag'] = $tag;
        $where['status'] = RecommendModel::TYPE2;
        if (!empty($hid)) {
            $where['hid'] = $hid;
        }
        if (!empty($gid)) {
            $where['gid'] = $gid;
        }
        if (!empty($city)) {
            $where['city'] = $city;
        }
        $count = $recommendModel->where($where)->count();

        $num = ceil($count/$page_size);
        if ($page > $num) {
            $list = [];
        } else {
            $list = $recommendModel->where($where)->field('id recommend_id,title,tag,start_time,end_time,address,img,jump_url,views')->order('sort', 'asc')->limit(($page - 1)*$page_size, $page_size)->select()->toArray();

        }
        $this->success('', ['count' => $count, 'list' => $list]);
    }

    /***
     * Action 峰会列表页[首页峰会回顾点击更多请求此接口]
     * @author ywf
     * @license /api/homepage/summit POST
     * @para string page  页面数,默认1|Y
     * @para string page_size  一页显示条数,默认10|N
     * @field string code   1:成功;0:失败
     * @field string msg    无提示
     * @field string data.count   会议总数
     * @field string data.list    会议列表，为空表示无暂无会议
     * @field string list.summit_id 会议id
     * @field string list.name    会议名称
     * @field string list.img     会议封面图
     * @field string list.start_time  会议开始时间
     * @field string list.end_time  会议结束时间
     * @field string list.address 地址
     * @field string list.number  会议规模
     * @field string list.status  会议状态：0已结束，1进行中，2未开始
     * @field string list.status_str  已结束，进行中，未开始
     * @field string list.jump_url  跳转会议[查看详情使用]
     * @jsondata {"page":"1"}
     * @jsondatainfo {"code":1,"msg":"","time":"1579589161","data":{"count":2,"list":[{"summit_id":1,"name":"破·局 MS2019","img":"http:\/\/poju.com\/upload\/image\/2019-10\/e432af9e40ed08de4a20fdd2ea7a7ab1.png","start_time":"2019.11.21","end_time":"2019.11.30","address":"","number":0,"status":"0","status_str":"已结束"},{"summit_id":2,"name":"测试","img":"http:\/\/poju.com\/upload\/image\/2019-11\/9d0a16a0896c91c1142c3b45d2438858.jpg","start_time":"0000.00.00","end_time":"0000.00.00","address":"","number":0,"status":"0","status_str":"已结束"}]}}
     */
    public function summit()
    {
        $where['state'] = 1; //开放中的

        $page = $this->request->post('page', 1, 'intval');
        $page_size = $this->request->post('page_size', 10, 'intval');
        $count = Db::name('category')->where($where)->whereNull('delete_time')->count();
        $num = ceil($count/$page_size);
        if ($page > $num) {
            $list = [];
        } else {
            $list = Db::name('category')->where($where)->whereNull('delete_time')->field('id summit_id,name,img,start_time,end_time,address,number,profile,banner,realm_name theme')->order('sort', 'asc')->limit(($page - 1)*$page_size, $page_size)->select();
        }
        $host = request()->root(true);
        array_walk($list, function (&$v) use($host) {
            $v['name'] = htmlspecialchars_decode($v['name']);
            $v['profile'] = htmlspecialchars_decode($v['profile']);
            if ($v['banner']) {

                $v['img'] =  $v['banner'] && strpos($v['banner'], 'http') !== false ? $v['banner'] : $host . $v['banner'];
            } else {
                $v['img'] =  $v['img'] && strpos($v['img'], 'http') !== false ? $v['img'] : $host . $v['img'];
            }

            if (strtotime($v['end_time']) < time()) {
                $v['status'] = '0'; //已结束
                $v['status_str'] = '已结束'; //已结束
            }

            if (strtotime($v['end_time']) > time() && time() > strtotime($v['start_time'])) {
                $v['status'] = '1'; //进行中
                $v['status_str'] = '进行中'; //进行中
            } else if(strtotime($v['start_time']) > time()){
                $v['status'] = '2'; //未开始
                $v['status_str'] = '未开始'; //未开始
            }

            $v['start_time'] = str_replace('-', '.', $v['start_time']);
            $v['end_time'] = str_replace('-', '.', $v['end_time']);
            $v['jump_url'] = $host .'/summit/' . $v['summit_id'];

        });
        $this->success('', ['count' => $count, 'list' => $list]);
    }

    /***
     * Action  会议详情
     * @author ywf
     * @license /api/homepage/summitInfo POST
     * @para string summit_id  会议id|Y
     * @field string code   1:成功;0:失败
     * @field string msg    成功
     * @field string model    会议信息
     * @field string guest    演讲嘉宾
     * @field string builder    共建人
     * @field string agenda    峰会议程
     * @field string contact    联系方式
     * @field string ads    往期回顾
     * @field string cooper    合作伙伴
     * @field string videos    视频详情
     * @field string summit_id    会议id
     * @jsondata {"mobile_prefix":"86","mobile":"18339817892","code":"123456","new_password":"123456"}
     * @jsondatainfo {"code":1,"msg":"密码已修改","time":"1581157326","data":null}
     */
    public function summitInfo()
    {
        $categoryModel = new Category();
        $sid = $this->request->post('summit_id', 0, 'intval');
        if(empty($sid)){
            $infoModel = $categoryModel->where(['state' =>1])->order('sort asc')->find();
        }else{
            $infoModel = $categoryModel->where(['id' => $sid])->find();
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
        $host = request()->root(true);
        foreach ($cooperList as &$v) {
            if (!empty($v['imglist'])) {
                $imgs = explode(',', $v['imglist']);
                $new_img_url = [];
                foreach ($imgs as $vv) {
                    $new_img_url[] = $vv && strpos($vv, 'http') !== false ? $vv : $host . $vv;

                }

                $v['imglist'] = implode(',', $new_img_url);

            }

        }
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
        $sc = new SCModel();
        $SysConfig = $sc->column('value','name');
        $host = request()->root(true);
        if (!empty($SysConfig['gzh'])) {
            $SysConfig['gzh'] =  $SysConfig['gzh'] && strpos($SysConfig['gzh'], 'http') !== false ? $SysConfig['gzh'] : $host . $SysConfig['gzh'];
        }
        if (!empty($SysConfig['prize_banner'])) {
            $SysConfig['prize_banner'] =  $SysConfig['prize_banner'] && strpos($SysConfig['prize_banner'], 'http') !== false ? $SysConfig['prize_banner'] : $host . $SysConfig['prize_banner'];
        }
        if (!empty($SysConfig['wx_img'])) {
            $SysConfig['wx_img'] =  $SysConfig['wx_img'] && strpos($SysConfig['wx_img'], 'http') !== false ? $SysConfig['wx_img'] : $host . $SysConfig['wx_img'];
        }

        $info = [
            'model'=>$infoModel,
            'guest'=>$guestList,
            'builder'=>$builder,
            'agenda'=>$agendaItems,
            'contact'=>$lx,
            'ads'=>$adsList,
            'cooper'=>$cooperList,
            'videos'=>$list,
            'summit_id' => $infoModel['id'],//会议id
            'sys_config' => $SysConfig,
        ];

        $this->success('', ['info' => $info]);
    }

    /***
     * Action  订阅会议动态
     * @author ywf
     * @license /api/homepage/feedback POST
     * @field string code   1:成功;0:失败
     * @field string msg    成功
     * @jsondata {"mobile_prefix":"86","mobile":"18339817892","code":"123456","new_password":"123456"}
     * @jsondatainfo {"code":1,"msg":"密码已修改","time":"1581157326","data":null}
     */
    public function feedback()
    {
        $data = $this->request->post();
        $data['subdate'] = time();
        $data['id'] = 0;
        $fb = new Feedback();
        $res = $fb->allowField(true)->isUpdate(false)->save($data);
        if($res!==false){
            $this->success('提交成功');
        }else{
            $this->error('服务器繁忙，请稍后重试！');
        }
    }

    /***  获取动态表单信息
     * Action
     * @author ywf
     * @license /api/homepage/getForm POST
     * @para string summit_id  会议id|Y
     * @field string code   1:成功;0:失败
     * @field string msg    成功
     * @jsondata {"mobile_prefix":"86","mobile":"18339817892","code":"123456","new_password":"123456"}
     * @jsondatainfo {"code":1,"msg":"密码已修改","time":"1581157326","data":null}
     */
    public function getForm()
    {
        $category = new Category();
        $id = $this->request->param('summit_id');
        $model = $category->where(['id' => $id])->find();
        if(empty($model)){
            $this->error('请求出错了');
        }
        $this->db_app = Db::connect('database_morketing');
        $elemItem = [];
        if (!empty($model['diy_form'])) {
            $idList = explode(',',$model['diy_form']);
            $elemItem = $this->db_app->table('diy_form')->where('id', 'in', $idList)->select();

            foreach ($elemItem as &$v) {
                if (isset($this->user[$v['name']]) && !empty($this->user[$v['name']])) {
                    $v['value'] = $this->user[$v['name']];
                }
            }
        }

        $this->success('', [
            'diy_form_list' => $elemItem,
        ]);

    }

    /***  报名提交
     * Action
     * @author ywf
     * @license /api/homepage/subInfo POST
     * @para string summit_id  会议id|Y
     * @para string user_id  用户id|Y
     * @field string code   1:成功;0:失败
     * @field string msg    成功
     * @jsondata {"mobile_prefix":"86","mobile":"18339817892","code":"123456","new_password":"123456"}
     * @jsondatainfo {"code":1,"msg":"密码已修改","time":"1581157326","data":null}
     */
    public function subInfo()
    {
        $postData = $this->request->post();
        //查看该用户是否已经报名
        $is_post = Db::name('summit_enroll')->where(['user_id' => $postData['user_id'], 'cid' => $postData['summit_id']])->find();
        if (!empty($is_post)) {
            $this->error('该会议您已报名');
        }
        $this->db_app = Db::connect('database_morketing');
        //获取会议id
        $cid = $postData['summit_id'];
        $postData['cid'] = $postData['summit_id'];
        unset($postData['summit_id']);
        if (empty($cid)) {
            $this->error('出现未知错误');
        }
        $category = new Category();
        $diy_form = $category->where(['id' => $cid])->value('diy_form');
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
                        $this->error($msg);
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
            $this->error('报名失败请重试');
        }
        $this->success('报名成功');
    }


    /***
     * Action  获取更多共创人/演讲嘉宾
     * @author ywf
     * @license /api/homepage/getMoreGuest POST
     * @para string summit_id  会议id|Y
     * @para string type  0演讲嘉宾，1共创人|Y
     * @para string page  页数|Y
     * @field string code   1:成功;0:失败
     * @field string msg    成功
     * @jsondata {"mobile_prefix":"86","mobile":"18339817892","code":"123456","new_password":"123456"}
     * @jsondatainfo {"code":1,"msg":"密码已修改","time":"1581157326","data":null}
     */
    public function getMoreGuest()
    {
        $page = $this->request->param('page');
        $sid = $this->request->param('type');
        $cid = $this->request->param('summit_id');
        $guest = new Guest();
        $where['sid'] = $sid;
        $where['cid'] = $cid;
        $guestList = $guest->where($where)->with('category')->order('sort','asc')->paginate(12,false,['page'=>$page]);
        $this->success('', ['list' => $guestList]);
    }

    /***
     * Action 视频列表页[首页后续报道/视频报道点击更多请求此接口]
     * @author ywf
     * @license /api/homepage/video POST
     * @para string page  页面数,默认1|Y
     * @para string page_size  一页显示条数,默认10|N
     * @field string code   1:成功;0:失败
     * @field string msg    无提示
     * @field string data.count   视频总数
     * @field string data.list    视频列表
     * @field string list.video_id 视频id
     * @field string list.title    视频标题
     * @field string list.tag     视频标签
     * @field string list.profile 视频简介
     * @field string list.img     视频封面图
     * @field string list.views   浏览数
     * @field string list.likes   点赞数
     * @field string list.release_user   发布人名字
     * @field string list.avatar   发布人头像
     * @field string list.create_time   创建时间
     * @jsondata {"page":"1"}
     * @jsondatainfo {"code":1,"msg":"","time":"1579589246","data":{"count":2,"list":[{"video_id":4,"title":"视频报道1","tag":"视频报道","profile":"放松放松的","img":"http:\/\/poju.com\/upload\/image\/2020-01\/d46a8c29b2b33b2ae78c4acb89215834.png","views":3,"likes":1,"release_user":"admin","create_time":"2020-01-15 16:31:54","avatar":"\/static\/api\/img\/avatar.png"},{"video_id":5,"title":"视频报道2","tag":"视频报道","profile":"是发大水发大水","img":"http:\/\/poju.com\/upload\/image\/2020-01\/d46a8c29b2b33b2ae78c4acb89215834.png","views":0,"likes":0,"release_user":"admin","create_time":"2020-01-15 16:31:57","avatar":"\/static\/api\/img\/avatar.png"}]}}
     */
    public function video()
    {
        $where['type'] = Report::TYPE2;
        $page = $this->request->post('page', 1, 'intval');
        $page_size = $this->request->post('page_size', 10, 'intval');
        $count = Db::name('report')->where($where)->count();
        $num = ceil($count/$page_size);
        $host = request()->root(true);
        if ($page > $num) {
            $list = [];
        } else {
            $list = Db::name('report')->where($where)->field('id video_id,title,tag,profile,img,views,likes,release_user,create_time')->order('sort', 'asc')->limit(($page - 1)*$page_size, $page_size)->select();
            $admin = new SysAdmin();
            $host = request()->root(true);
            array_walk($list, function(&$v) use ($admin, $host) {
                $v['title'] = htmlspecialchars_decode($v['title']);
                $v['profile'] = htmlspecialchars_decode($v['profile']);
                if ($v['img'] && strpos($v['img'], 'http') === false)
                {
                    $v['img'] =  $host . $v['img'];
                }
                $admin_info = $admin->where(['id' => $v['release_user']])->find();
                $v['release_user'] = $admin_info['account'];
                $v['avatar'] = isset($admin_info['avatar']) && !empty($admin_info['avatar']) ? $host . $admin_info['avatar']: $host . '/static/api/img/avatar.png';
            });

        }

        $this->success('', ['count' => $count, 'list' => $list]);

    }

    /***
     * Action 视频详情页
     * @author ywf
     * @license /api/homepage/videoDetail POST
     * @para string video_id  视频id|Y
     * @field string code   1:成功;0:失败
     * @field string msg    无提示
     * @field string data.video_info 视频信息
     * @field string list.video_id 视频id
     * @field string list.title    视频标题
     * @field string list.tag     视频标签
     * @field string list.profile 视频简介
     * @field string list.img     视频封面图
     * @field string list.play_url   视频播放地址
     * @field string list.views   浏览数
     * @field string list.likes   点赞数
     * @field string list.collections   点赞数
     * @field string list.release_user   发布人名字
     * @field string list.avatar   发布人头像
     * @field string list.create_time   创建时间
     * @field string data.collections [用户已登录]，是否收藏过该视频，0，未收藏，1：已收藏。[未登录]：返回0
     * @field string data.likes [用户已登录]，是否点赞过该视频，0，未点赞，1：已点赞。[未登录]：返回0
     * @jsondata {"video_id":"4"}
     * @jsondatainfo {"code":1,"msg":"","time":"1579589762","data":{"video_info":{"video_id":4,"title":"视频报道1","tag":"视频报道","profile":"放松放松的","type":2,"img":"http:\/\/poju.com\/upload\/image\/2020-01\/d46a8c29b2b33b2ae78c4acb89215834.png","play_url":"www.baidu.com","views":11,"likes":1,"collections":1,"release_user":"admin","create_time":"2020-01-15 16:31:54","avatar":"\/static\/api\/img\/avatar.png"},"collections":0,"likes":0}}
     */
    public function videoDetail()
    {
        //浏览量加1
        $video_id = $this->request->post('video_id');
        $reportModel = new Report();
        $reportModel->where(['id' => $video_id])->setInc('views',1);

        if (empty($video_id)) {
            $this->error('页面迷路了~~');
        }

        $video_info = Db::name('report')->where(['id' => $video_id])->field('id video_id,title,tag,profile,type,img,jump_url play_url,views,likes,collections,release_user,create_time')->find();
        $video_info['title'] = htmlspecialchars_decode($video_info['title']);
        $video_info['profile'] = htmlspecialchars_decode($video_info['profile']);
        $video_info['play_url'] = htmlspecialchars_decode($video_info['play_url']);
        $host = request()->root(true);
        $video_info['img'] = $host . $video_info['img'];

        if (empty($video_info) || $video_info['type'] == 1) {
            $this->error('页面迷路了~~');
        }
        $host = request()->root(true);
        $admin = new SysAdmin();
        $admin_info = $admin->where(['id' => $video_info['release_user']])->find();
        $video_info['release_user'] = $admin_info['account'];
        $video_info['avatar'] = isset($admin_info['avatar']) && !empty($admin_info['avatar']) ? $host . $admin_info['avatar'] : $host . '/static/api/img/avatar.png';
        //判定用户是否登录
        $collection = $likes = 0;
        if ($this->user) {
            //获取用户是否收藏过
            $info = $this->db_app->table('video_collection')->where(['uid' => $this->user->id, 'video_id' => $video_id])->find();
            if (!empty($info)) {
                $collection = 1;
            }
            //查询用户是否点过赞
            $info2 = $this->db_app->table('video_likes')->where(['uid' => $this->user->id, 'video_id' => $video_id])->find();
            if (!empty($info2)) {
                $likes = 1;
            }

        }
        $this->success('', ['video_info' => $video_info, 'collections' => $collection, 'likes' =>$likes ]);

    }

    /***
     * Action 视频详情页/最近视频列表
     * @author ywf
     * @license /api/homepage/latelyVideoList POST
     * @para string video_id  页面数,默认1|Y
     * @para string page  页面数,默认1|Y
     * @para string page_size  一页显示条数,默认10|N
     * @field string code   1:成功;0:失败
     * @field string msg    无提示
     * @field string data.count   视频总数
     * @field string data.list    视频列表
     * @field string list.video_id 视频id
     * @field string list.title    视频标题
     * @field string list.tag     视频标签
     * @field string list.profile 视频简介
     * @field string list.img     视频封面图
     * @field string list.create_time   发布时间
     * @jsondata {"page":"1"}
     * @jsondatainfo {"code":1,"msg":"","time":"1579589943","data":{"count":1,"list":[{"video_id":5,"title":"视频报道2","tag":"视频报道","profile":"是发大水发大水","img":"http:\/\/poju.com\/upload\/image\/2020-01\/d46a8c29b2b33b2ae78c4acb89215834.png","create_time":"2020-01-15 16:31:57"}]}}
     */
    public function  latelyVideoList()
    {
        $video_id = $this->request->post('video_id');
        $where['type'] = Report::TYPE2;
        $where['id'] = ['neq', $video_id];
        $page = $this->request->post('page', 1, 'intval');
        $page_size = $this->request->post('page_size', 10, 'intval');
        $count = Db::name('report')->where($where)->count();
        $num = ceil($count/$page_size);
        if ($page > $num) {
            $list = [];
        } else {
            $list = Db::name('report')->where($where)->field('id video_id,title,tag,profile,img,create_time')->order('create_time', 'desc')->limit(($page - 1)*$page_size, $page_size)->select();
            $host = request()->root(true);
            array_walk($list, function(&$v) use ($host) {
                $v['title'] = htmlspecialchars_decode($v['title']);
                $v['profile'] = htmlspecialchars_decode($v['profile']);
                if ($v['img'] && strpos($v['img'], 'http') === false)
                {
                    $v['img'] =  $host . $v['img'];
                }

            });

        }

        $this->success('', ['count' => $count, 'list' => $list]);
    }


    /***
     * Action 视频详情页获取评论
     * @author ywf
     * @license /api/homepage/comment POST
     * @para string video_id  页面数,默认1|Y
     * @para string page  页面数,默认1|Y
     * @para string page_size  一页显示条数,默认10|N
     * @field string code   1:成功;0:失败
     * @field string msg    无提示
     * @field string data.count   评论总数
     * @field string data.list    评论列表
     * @field string list.comment_id 评论id
     * @field string list.user_id 用户id
     * @field string list.video_id 视频id
     * @field string list.info    评论内容
     * @field string list.subtime 评论时间
     * @field string list.avatar  用户头像
     * @field string list.nickname  用户昵称
     * @field string list.son_comment 评论下的子评论
     * @field string list.son_comment.to_user 不空代表回复的评论
     * @field string list.son_comment.cid 楼主评论id
     * @jsondata {"video":"4"}
     * @jsondatainfo {"code":1,"msg":"","time":"1579590469","data":{"count":2,"list":[{"comment_id":1,"user_id":2385,"video_id":4,"info":"用户4的评论","subtime":"2020-01-17 14:31","avatar":"http:\/\/118.zwtppt.com\/static\/backend\/images\/avatar.png","nickname":"闫伟峰","son_comment":[{"comment_id":3,"user_id":2387,"video_id":4,"info":"评论2385","to_user":"","subtime":"2020-01-17 14:31","cid":1,"avatar":"http:\/\/118.zwtppt.com\/static\/backend\/images\/avatar.png","nickname":"yan"},{"comment_id":4,"user_id":2385,"video_id":4,"info":"回复2387","to_user":"yan","subtime":"2020-01-17 14:31","cid":1,"avatar":"http:\/\/118.zwtppt.com\/static\/backend\/images\/avatar.png","nickname":"闫伟峰"},{"comment_id":5,"user_id":2387,"video_id":4,"info":"回复2385","to_user":"闫伟峰","subtime":"2020-01-17 14:53","cid":1,"avatar":"http:\/\/118.zwtppt.com\/static\/backend\/images\/avatar.png","nickname":"yan"}]},{"comment_id":2,"user_id":2387,"video_id":4,"info":"dddd","subtime":"2020-01-17 14:31","avatar":"http:\/\/118.zwtppt.com\/static\/backend\/images\/avatar.png","nickname":"yan","son_comment":[]}]}}
     */
    public function comment()
    {
        $video_id = $this->request->post('video_id');
        $page = $this->request->post('page', 1, 'intval');
        $page_size = $this->request->post('page_size', 10, 'intval');
        $where['cid'] = 0;
        $where['video_id'] = $video_id;
        $count = $this->db_app->table('video_comment')->where($where)->count();
        $num = ceil($count/$page_size);
        if ($page > $num) {
            $list = [];
        } else {
            $list = $this->db_app->table('video_comment')->where($where)->field('id comment_id,uid user_id,video_id,info,subtime')->order('subtime', 'desc')->limit(($page - 1)*$page_size, $page_size)->select();
            $host =  Config::get('morketing_avatar_url');
            foreach ($list as $k => $v) {
                //获取用户的昵称和头像
                $user_info = $this->db_app->table('user')->where(['id' => $v['user_id']])->field('avatar,nickname')->find();
                if ($user_info['avatar'] && strpos($user_info['avatar'], 'http') === false)
                {
                    $list[$k]['avatar'] = $host.$user_info['avatar'];
                } else {
                    $list[$k]['avatar'] = $user_info['avatar'];
                }

                $list[$k]['nickname'] = $user_info['nickname'];
                $list[$k]['subtime'] = date('Y-m-d H:i', $v['subtime']);
                //获取子评论
                $son_comment =  $this->db_app->table('video_comment')->where(['cid' => $v['comment_id']])->field('id comment_id,uid user_id,video_id,info,to_uid to_user,subtime,cid')->order('subtime', 'ASC')->select();
                foreach ($son_comment as $kk => $vv) {
                    //获取用户的昵称和头像
                    $user_info2 = $this->db_app->table('user')->where(['id' => $vv['user_id']])->field('avatar,nickname')->find();
                    if ($user_info2['avatar'] && strpos($user_info2['avatar'], 'http') === false)
                    {
                        $son_comment[$kk]['avatar'] = $host . $user_info2['avatar'];
                    } else {
                        $son_comment[$kk]['avatar'] = $user_info2['avatar'];
                    }
                    $son_comment[$kk]['nickname'] = $user_info2['nickname'];
                    $son_comment[$kk]['subtime'] = date('Y-m-d H:i', $vv['subtime']);
                    $son_comment[$kk]['cid'] = $vv['cid']; //挂载评论id
                    if (!empty($vv['to_user'])) {
                        //回复的评论
                        $user_info3 = $this->db_app->table('user')->where(['id' => $vv['to_user']])->field('avatar,nickname')->find();
                        $son_comment[$kk]['to_user'] = $user_info3['nickname'];
                    } else {
                        $son_comment[$kk]['to_user'] = '';
                    }

                }
                $list[$k]['son_comment'] = $son_comment;
            }

        }

        $this->success('', ['count' => $count, 'list' => $list]);


    }

    /***
     * Action 提交评论[须用户登录]
     * @author ywf
     * @license /api/homepage/addComment POST
     * @para string video_id  页面数,默认1|Y
     * @para string user_id  提交评论用户id|Y
     * @para string to_user  评论：默认为0，回复：为回复用户id|Y
     * @para string cid      评论：默认为0，回复：为本层楼主评论id|Y
     * @para string info     评论内容|Y
     * @field string code   1:成功;0:失败
     * @field string msg    code=0:评论失败，请重试。code=1:已提交
     * @jsondata {"code":1,"msg":"已提交","time":"1579244001","data":null}
     */
    public function addComment()
    {
        $post = $this->request->post();

        if (empty($post['info'])) {
            $this->error('评论内容不能为空');
        }

        $data = [
            'video_id' => $post['video_id'],
            'uid' => $post['user_id'],
            'info' => htmlspecialchars($post['info']),
            'to_uid' => $post['to_user'],
            'cid' => $post['cid'],
            'subtime' => time(),
            'status' => 1,

        ];
        $res = $this->db_app->table('video_comment')->insert($data);
        if ($res === false) {
            $this->error('评论失败，请重试');
        }

        $this->success('已提交');

    }

    /***
     * Action 收藏[需要用户登录]
     * @author ywf
     * @license  /api/homepage/collection POST
     * @para string video_id  视频id|Y
     * @para string user_id  会员id|Y
     * @field string code   1:成功;0:失败
     * @field string msg    code=0:收藏失败，请重试！。code=1:收藏成功,
     * @field string data.collection_id    收藏id
     * @field string data.collection_count    收藏总数
     * @jsondata {"video_id":"4","user_id":"2385"}
     * @jsondatainfo {"code":1,"msg":"收藏成功","time":"1579244580","data":{"collection_count":1}}
     */
    public function collection()
    {
        $video_id = $this->request->post('video_id');
        $user_id = $this->request->post('user_id');
        //查看用户是否收藏过该
        $is_collection = $this->db_app->table('video_collection')->where(['video_id' => $video_id, 'uid' => $user_id])->find();
        $count = Db::name('report')->where(['id' => $video_id])->value('collections');
        if ($is_collection) {
            $this->success('收藏成功', ['collection_count' => $count]);
        }
        Db::name('report')->where(['id' => $video_id])->setInc('collections',1);
        $data = [
            'uid' => $user_id,
            'video_id' => $video_id,
            'create_time' => date('Y-m-d H:i:s'),
        ];
        $res2 =  $this->db_app->table('video_collection')->insertGetId($data);
        if ($res2 === false) {
            $this->error('收藏失败，请重试！');
        }
        $count = Db::name('report')->where(['id' => $video_id])->value('collections');

        $this->success('收藏成功', ['collection_count' => $count]);
    }

    /***
     * Action 点赞[需要用户登录]
     * @author ywf
     * @license /api/homepage/giveLike POST
     * @para string video_id  视频id|Y
     * @para string user_id  会员id|Y
     * @field string code   1:成功;0:失败
     * @field string msg    code=0:点赞失败，请重试！。code=1:点赞成功,
     * @field string data.likes_count    点赞总数
     * @jsondata {"video_id":"4","user_id":"2385"}
     * @jsondatainfo {"code":1,"msg":"点赞成功","time":"1579244783","data":{"likes_count":1}}
     */
    public function giveLike()
    {
        $video_id = $this->request->post('video_id');
        $user_id = $this->request->post('user_id');
        //查看用户是否收藏过该
        $is_collection = $this->db_app->table('video_likes')->where(['video_id' => $video_id, 'uid' => $user_id])->find();
        $count = Db::name('report')->where(['id' => $video_id])->value('likes');
        if ($is_collection) {
            $this->success('点赞成功',['likes_count' => $count]);
        }
        Db::name('report')->where(['id' => $video_id])->setInc('likes',1);
        $data = [
            'uid' => $user_id,
            'video_id' => $video_id,
            'create_time' => date('Y-m-d H:i:s'),
        ];
        $res2 = $is_collection = $this->db_app->table('video_likes')->insert($data);
        if ($res2 === false) {
            $this->error('点赞失败，请重试！');
        }
        $count = Db::name('report')->where(['id' => $video_id])->value('likes');
        $this->success('点赞成功',['likes_count' => $count]);
    }

    /***
     * Action 底部版权信息
     * @author ywf
     * @license /api/homepage/copyright POST
     * @para string 无|N
     * @field string code   1:成功;
     * @field string data.copyright   版权信息
     * @field string data.beian   icp备案号
     * @jsondata
     * @jsondatainfo {"code":1,"msg":"","time":"1582792362","data":{"title":"破·局 | MS2019-全球营销商业峰会","description":"有破有立，突破棋局","intro":"Morketing Summit系全球营销内容信息服务平台Morketing旗下的活动品牌，延承Morketing大愿景“营销连接商业世界”，从营销视角覆盖品牌、游戏、电商、应用、数据、技术、和全球化等多板块。\r\n在前两届峰会上，国内外近两百位行业领袖登台演讲，现已成为全球商业的顶尖营销盛会。\r\n","zanzhu":"yangshanshan@morketing.com、qinwei@morketing.com","copyright":"COPYRIGHT © 2014-2019  Morketing版权所有","beian":"京ICP备16042578号-1","gzh":"\/upload\/image\/2018-09\/707bd5eb157f988ca3baba375d1c5e23.jpg","wx_img":"\/upload\/image\/2019-11\/23ec0a24c0ab1bedd48b3552a1471b86.jpg","prize_banner":"\/upload\/image\/2018-11\/0c57f73f5dd8180685487bc3ef52b1e7.png"}}
     */
    public function copyright()
    {
        $sc = new SCModel();
        $sys_config = $sc->column("value","name");
        $this->success('', $sys_config);

    }

    /***
     * Action 查看近期推荐，并返回最新浏览量
     * @author ywf
     * @license /api/homepage/recommendViews POST
     * @para string recommend_id  近期推荐id|N
     * @field string code   1:成功;
     * @field string data.views   浏览量
     * @jsondata {"recommend_id":"1"}
     * @jsondatainfo
     */
    public function recommendViews()
    {
        $recommend_id = $this->request->post('recommend_id');
        Db::name('recommend')->where(['id' => $recommend_id])->setInc('views', 1);
        $count = Db::name('recommend')->where(['id' => $recommend_id])->value('views');
        $this->success('',['views' => $count]);

    }



    /***
     * Action 网站logo
     * @author ywf
     * @license /api/homepage/webLogo POST
     * @para string 无
     * @field string code   1:成功;
     * @jsondata
     * @jsondatainfo
     */
    public function webLogo()
    {
        $sc = new SCModel();
        $sys_config = $sc->column("value","name");
        $host = request()->root(true);
        if (empty($sys_config['logo'])) {
            $logo = $host. '/static/backend/images/sys_logo2.png';
        } else {
            $logo = $host. $sys_config['logo'];
        }

        $this->success('',['logo' => $logo]);
    }

    /***
     * Action banner图广告位
     * @author ywf
     * @license /api/homepage/bannerAdvert POST
     * @para string 无
     * @field string code   1:成功;
     * @jsondata 空
     * @jsondatainfo
     */
    public function bannerAdvert()
    {
        $sp = new Special();

        $banner_advert = $sp->where(['status' => 0])->order('displayorder ASC')->field('name,img,url')->find();
        $host = request()->root(true);
        if (empty($banner_advert)) {
            $banner_advert = [];
        } else {
            $banner_advert['img'] = $host. $banner_advert['img'];
        }
        $this->success('',['banner_advert' => $banner_advert]);
    }

    /***
     * Action 查看顶部banner，并返回最新浏览量banner
     * @author ywf
     * @license /api/homepage/bannerViews POST
     * @para string banner_id   banner列表id|Y
     * @field string code   1:成功;
     * @field string data.views   浏览量
     * @jsondata {"banner_id":"1"}
     * @jsondatainfo
     */
    public function bannerViews()
    {
        $recommend_id = $this->request->post('banner_id');
        Db::name('summit_banner')->where(['id' => $recommend_id])->setInc('views', 1);
        $count = Db::name('summit_banner')->where(['id' => $recommend_id])->value('views');
        $this->success('',['views' => $count]);

    }

    /***
     * Action 查看用户协议
     * @author ywf
     * @license /api/homepage/userTreaty POST
     * @para string 无|N
     * @field string code   1:成功;
     * @field string data.content   用户协议
     * @jsondata
     * @jsondatainfo {"treaty":"xxx"}
     */
    public function userTreaty()
    {
        $sc = new SCModel();
        $sys_config = $sc->column("value","name");
        $content = '';
        if (isset($sys_config['treaty']) && !empty($sys_config['treaty'])) {
            $content = $sys_config['treaty'];
        }
        $this->success('', ['treaty' => $content]);

    }
}