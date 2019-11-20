<?php
/**
 * Created by PhpStorm.
 * User: Still范特西
 * Date: 2018-06-20
 * Time: 16:32
 */
namespace app\index\controller;
use app\backstage\model\Category;
use think\Controller;
use app\backstage\model\SysConfig as SCModel;
use think\View;
class WebBase extends Controller
{
    protected $category;
    protected function _initialize()
    {
        $sc = new SCModel();
        $this->category = new Category();
        $SysConfig = $sc->column('value','name');
        View::share('SysConfig', $SysConfig);
    }
}