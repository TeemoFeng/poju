<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    /*'[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],*/

     //联系
    '/about' => 'index/Index/about',
    '/contact' => 'index/Index/contact',
    '/copyright' => 'index/Index/copyright',
    //文章
    '/article/[:id]' => 'index/Article/items',
    '/detail/:id'=>'index/Article/detail',
    '/member/:id'=>'index/Article/member',
    //404
    '/404'=>'index/Index/errorPage',
    //快讯
    '/live/[:id]' => 'index/Flash/items',
    '/live-item/:id' => 'index/Flash/detail',
    //报告
    '/report/[:id]' => 'index/Library/report',
    '/report-item/:id' => 'index/Library/detail',
    //百科
    '/baike/[:id]'=>'index/Library/baike',
    '/baike-item/:id'=>'index/Library/item',
     //活动
    '/activity/[:id]' => 'index/activity/items',
    //搜索
    '/search' => 'index/Search/index',
    //会员中心
    '/user/login' =>'index/User/login',
    '/user/register' =>'index/User/register',
    '/user/check_account'=>'index/User/check_account',
    '/user/setting'=>'index/User/infocenter',
    '/user/comment'=>'index/User/comment',
    '/user/collection'=>'index/User/collection',
    '/user/verify_email'=>'index/User/verify_email',
    '/user/find_pwd'=>'index/User/find_pwd',
    '/user/logout'=>'index/User/logout',
    //交互
    '/digg/like'=>'index/digg/like',
    '/digg/collect'=>'index/digg/collect',
    '/digg/bull'=>'index/digg/bull',
    '/digg/bear'=>'index/digg/bear',
    '/digg/is_login'=>'index/digg/is_login',
    '/digg/add_comment'=>'index/digg/add_comment',
    '/digg/getComment'=>'index/digg/getComment',
];
