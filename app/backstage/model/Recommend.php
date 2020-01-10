<?php
/**
 * Created by PhpStorm.
 * User: Uroaming
 * Date: 2020/1/10
 * Time: 17:43
 */

namespace app\backstage\model;

class Recommend extends BaseModel
{
    public static $types = [
        '1' => '固定强推广告位',
        '2' => '普通轮播广告位',
    ];

    public static $status = [
        '1' => '下线',
        '2' => '上线',
    ];
}