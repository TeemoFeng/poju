<?php
namespace app\backstage\controller;
use app\common\controller\Base;
use think\Db;
class Index extends Base
{
    public function index()
    {
        $_version = Db::query('select version() as ver');
        $this->assign("mysql_ver",array_pop($_version)['ver']);
        return $this->fetch();
    }
}