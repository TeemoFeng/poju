<?php
/**
 * Created by PhpStorm.
 * User: Uroaming
 * Date: 2020/1/14
 * Time: 9:26
 */

namespace app\api\controller;

use app\api\library\ApiBase;
use app\backstage\model\Report;
use app\backstage\model\Review;
use app\backstage\model\SummitBanner;
use app\backstage\model\Recommend as RecommendModel;
use app\backstage\model\SysAdmin;
use think\Config;
use think\Db;

/**
 * 用户
 * Class User
 * @package app\api\controller
 */
class Homepage extends ApiBase {

    /**
     * 无需登录的方法
     */
    protected $noNeedLogin = ['index', 'summit', 'video','videoDetail','latelyVideoList', 'comment', 'addComment', 'collection', 'giveLike', 'recommend'];

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
        //近期活动强推广告位4个
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
     * Action 近期推荐列表页
     * @author ywf
     * @license /api/homepage/recommend POST
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
        $page = $this->request->post('page', 1, 'intval');
        $page_size = $this->request->post('page_size', 28, 'intval');
        $recommendModel = new RecommendModel();
        $count = $recommendModel->count();

        $num = ceil($count/$page_size);
        if ($page > $num) {
            $list = [];
        } else {
            $list = $recommendModel->field('id recommend_id,title,tag,start_time,end_time,address,img,jump_url,views')->order('sort', 'asc')->limit(($page - 1)*$page_size, $page_size)->select()->toArray();

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
        $count = Db::name('category')->where($where)->count();
        $num = ceil($count/$page_size);
        if ($page > $num) {
            $list = [];
        } else {
            $list = Db::name('category')->where($where)->field('id summit_id,name,img,start_time,end_time,address,number')->order('sort', 'asc')->limit(($page - 1)*$page_size, $page_size)->select();
        }
        $host = request()->root(true);
        array_walk($list, function (&$v) use($host) {

            if ($v['img'] && strpos($v['img'], 'http') === false)
            {
                $v['img'] =  $host . $v['img'];
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
//            $host = request()->root(true);

        });
        $this->success('', ['count' => $count, 'list' => $list]);
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
        if ($page > $num) {
            $list = [];
        } else {
            $list = Db::name('report')->where($where)->field('id video_id,title,tag,profile,img,views,likes,release_user,create_time')->order('sort', 'asc')->limit(($page - 1)*$page_size, $page_size)->select();
            $admin = new SysAdmin();
            $host = request()->root(true);
            array_walk($list, function(&$v) use ($admin, $host) {
                if ($v['img'] && strpos($v['img'], 'http') === false)
                {
                    $v['img'] =  $host . $v['img'];
                }
                $admin_info = $admin->where(['id' => $v['release_user']])->find();
                $v['release_user'] = $admin_info['account'];
                $v['avatar'] = isset($admin_info['avatar']) && !empty($admin_info['avatar']) ?: '/static/api/img/avatar.png';
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
        $host = request()->root(true);
        $video_info['img'] = $host . $video_info['img'];

        if (empty($video_info) || $video_info['type'] == 1) {
            $this->error('页面迷路了~~');
        }
        $admin = new SysAdmin();
        $admin_info = $admin->where(['id' => $video_info['release_user']])->find();
        $video_info['release_user'] = $admin_info['account'];
        $video_info['avatar'] = isset($admin_info['avatar']) && !empty($admin_info['avatar']) ?: '/static/api/img/avatar.png';
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





}