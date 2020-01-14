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
    protected $noNeedLogin = ['index'];

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
     * @jsondata {"mobile":"18312345671","password":"123456","captcha":"123456","sid":"j8qcr3e2cgotaad6sepqbh13j6"}
     * @jsondatainfo {"code":1,"msg":"登录成功","time":"1572510481","data":{"userInfo":{},"token":"sdfsdfsdfsdf"}}
     */
    public function index()
    {
        $recommendModel = new RecommendModel();
        $summitModel = new SummitBanner();
        $reviewModel = new Review();
        $reportModel = new Report();
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
     * Action 峰会列表页[首页峰会回顾点击更多请求此接口]
     * @author ywf
     * @license /api/originator/summit POST
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
     * @field string list.status  会议状态：0已结束，1进行中，2未开始
     * @field string list.status_str  已结束，进行中，未开始
     * @field string list.address 会议地址
     * @field string list.number  会议规模
     * @field string list.summit_url  跳转会议[查看详情使用]
     * @jsondata {"page":"1"}
     * @jsondatainfo {"code":1,"msg":"","time":"1572596956","data":{"list":[{"summit_id":1,"name":"灵眸2018","img":"\/upload\/image\/2019-10\/e432af9e40ed08de4a20fdd2ea7a7ab1.png","start_time":"2019-11-02","end_time":"2019-11-03","status":"1"}]}}
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

        array_walk($list, function (&$v) {
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
            $host = request()->root(true);
            $v['img'] = $host . $v['img'];

        });
        $this->success('', ['count' => $count, 'list' => $list]);
    }


    /***
     * Action 视频列表页[首页后续报道/视频报道点击更多请求此接口]
     * @author ywf
     * @license /api/originator/video POST
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
     * @field string list.status  会议状态：0已结束，1进行中，2未开始
     * @field string list.status_str  已结束，进行中，未开始
     * @field string list.address 会议地址
     * @field string list.number  会议规模
     * @field string list.summit_url  跳转会议[查看详情使用]
     * @jsondata {"page":"1"}
     * @jsondatainfo {"code":1,"msg":"","time":"1572596956","data":{"list":[{"summit_id":1,"name":"灵眸2018","img":"\/upload\/image\/2019-10\/e432af9e40ed08de4a20fdd2ea7a7ab1.png","start_time":"2019-11-02","end_time":"2019-11-03","status":"1"}]}}
     */
    public function video()
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

        array_walk($list, function (&$v) {
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
            $host = request()->root(true);
            $v['img'] = $host . $v['img'];

        });
        $this->success('', ['count' => $count, 'list' => $list]);
    }

}