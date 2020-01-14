<?php
namespace app\api\controller;
use app\api\library\ApiBase;
use Tools\DocumentScript;
use think\Controller;

class Document extends Controller
{
    /**
     * 无需登录的方法
     */
    protected $noNeedLogin = ['index'];
    //获取公共接口
    public function index()
    {
        $version         = 2.0;
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $path        = substr(__FILE__, 0, strrpos(__FILE__, '\\'));
        }else{
            $path        = substr(__FILE__, 0, strrpos(__FILE__, '/'));
        }

        $arrayControllers = [];
        $top              = [
            'title'   => '接口文档',
            'auther'  => 'xxx',
            'version' => 'V' . $version,
        ];
        $doc_result       = DocumentScript::showActionListDoc($path, $arrayControllers);
//        $apiroot          = $_SERVER['REQUEST_SCHEME'].'://' . $_SERVER['HTTP_HOST'] . '/Open/Index/index';
        $apiroot          = '/api/document/index';
        $this->assign('top', $top);
        $this->assign('apiroot', $apiroot);
        $this->assign('data', $doc_result);
        return $this->fetch('index');
    }



}
