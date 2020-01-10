<?php
/**
 * Created by PhpStorm.
 * User: Uroaming
 * Date: 2020/1/10
 * Time: 19:41
 */
namespace app\backstage\model;

class Report extends BaseModel
{
    public static $types = [
        '1' => '图文报道',
        '2' => '文字报道',
    ];

}