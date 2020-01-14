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
     * @field string data.banner_list banner列表
     * @field string data.recommend_fixed    近期活动强推广告位4个
     * @field string data.recommend_ordinary    近期活动普通广告位10个
     * @field string data.review_list    主办方历届峰会回顾4个
     * @field string data.report_list    后续报道4个
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
        //后续报道
        $report_list =$reportModel->homepageReportList();

        $this->success("", ['banner_list' => $banner_list, 'recommend_fixed' => $recommend_fixed, 'recommend_ordinary' => $recommend_ordinary,'review_list' => $review_list, 'report_list' => $report_list]);

    }

    /***
     * Action 峰会列表页[首页峰会回顾点击更多请求此接口]
     * @author ywf
     * @license /api/homepage/index POST
     * @para string 无 无|N
     * @field string code   1:成功;0:失败
     * @field string msg    信息提示
     * @field string data.banner_list banner列表
     * @field string data.recommend_fixed    近期活动强推广告位4个
     * @field string data.recommend_ordinary    近期活动普通广告位10个
     * @field string data.review_list    主办方历届峰会回顾4个
     * @field string data.report_list    后续报道4个
     * @jsondata {"mobile":"18312345671","password":"123456","captcha":"123456","sid":"j8qcr3e2cgotaad6sepqbh13j6"}
     * @jsondatainfo {"code":1,"msg":"登录成功","time":"1572510481","data":{"userInfo":{},"token":"sdfsdfsdfsdf"}}
     */
    public function summitList()
    {

    }



}