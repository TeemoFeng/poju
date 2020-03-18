<?php
namespace app\common\controller;
use think\Controller;
use app\backstage\model\SysConfig as SCModel;
use think\View;

class Base extends Controller
{
    protected $AdminInfo;
    protected function _initialize()
    {
        $sc = new SCModel();
        $sys_config = $sc->column("value","name");
        view::share('SysConfig', $sys_config);
       if ($this->request->session("UserInfo")==null)
       {
           $this->redirect(url("/backstage/login"));
       }else{
           $this->AdminInfo = $this->request->session("UserInfo");
       }
    }
    protected function generate_pagebar($pagesize,$pagecount,$pageindex,$controller,$action)
    {
        $pageinfo="<span class=\"page - info\">共有" . $pagecount . "页，每页显示：" . $pagesize . "条</span>";
        if ($pagecount == 1)
        {
            return "<a title=\"首页\"><<</a><a title=\"上一页\"><</a><a>1</a><a title=\"下一页\">></a><a title=\"尾页\">>></a> <input type=\"text\" /> <button title=\"跳转\" class=\"btn\" data-max=\"1\">GO</button>";
        }
        else
        {
            $CurrentPageBtn = "<a>" . $pageindex ."</a>";
            $StartPage = "<a title=\"首页\" href=\"" . url("/backstage/".$controller."/".$action,["page"=>1]) . "\"><<</a>";
            $EndPage = "<a title=\"尾页\" href=\"" . url("/backstage/".$controller."/".$action, [ "page" => $pagecount ]) + "\">>></a>";
            $PreviousPage = "<a title=\"上一页\"><</a>";
            $NextPage = "<a title=\"下一页\">></a>";
            $Go = " <input type=\"text\" /> <button title=\"跳转\" class=\"btn\" data-max=\"" . $pagecount . "\">GO</button>";
            if ($pageindex > 1) {
                $PreviousPage = "<a title=\"上一页\" href=\"" . url("/backstage/".$controller."/".$action, [ "page" => $pageindex - 1 ]) + "\"><</a>";
            }
            if ($pageindex < $pagecount) {
                $NextPage = "<a title=\"下一页\" href=\"" . url("/backstage/".$controller."/".$action, [ "page" => $pageindex + 1 ]) + "\">></a>";
            }
            return $pageinfo . $StartPage . $PreviousPage . $CurrentPageBtn . $NextPage . $EndPage . $Go;
        }
    }
}