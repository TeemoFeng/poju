<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

// 定义应用目录
define('APP_PATH', __DIR__ . '/../app/');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
//header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: http://dist.morketing.com");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header('Access-Control-Allow-Headers:Origin,Content-Type,Accept,Token,X-Requested-With,device');
header("Access-Control-Allow-Credentials: true");
if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
    exit;
}
