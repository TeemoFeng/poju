<?php
/**
 * Created by PhpStorm.
 * User: Dr.loveumore
 * Date: 2018-05-22
 * Time: 09:52
 */

namespace app\backstage\controller;
use app\common\controller\Base;
use think\config;
use Grafika\Grafika;
class Ajax extends Base
{
    public function upload()
   {
       $file = request()->file('file');
       $path = $this->request->param('path');
       $isRep = $path ? true : false;
       $extList = Config::get('file_upload');
       if ($file){
           $ext='';
           $file_type = 0;
           foreach ($extList as $k => $v) {
               $file_type++;
               $key = $file->checkExt($v);
               if($key){
                   $ext= $k;
                   break;
               }
           }
          if(empty($ext)) {
              return ["code" => 2, "msg" =>"该类型文件不允许上传！"];
          }else{
              $path = $path ?: '/upload/'.$ext.'/' . date('Y-m');
          }
           $filepath = pathinfo($path);
           $info = $isRep?$file->move(ROOT_PATH . '/public'.$filepath['dirname'],$filepath['basename']) :$file->rule("md5")->move(ROOT_PATH . '/public'.$path);
           $resPath = $isRep?$filepath['dirname']:$path;
           $url = $resPath.'/'. $info->getSaveName();
           $handler = $this->request->param('handler');
           if (!empty($handler)){
               $param = $this->request->param();
               $this->Handler($handler,$url,$param);
           }
           return json(["code" => 1, "url"=>$url,"ft"=>$file_type]);
       }else{
           return ["code" => 2, "msg" =>"上传文件为空"];
       }
   }
    public function Handler($model,$path,$param)
    {
        $path = ROOT_PATH . '/public/' . $path;
        switch ($model){
            case 'gray':
                $this->Gray($path);
                break;
            case 'center':
                $this->resizeFill($path);
            default:
                return null;
        }
    }
    /*
    * 灰度处理
    */
    public function Gray($path)
    {
        $editor = Grafika::createEditor();
        $editor->open($image, $path );
        $filter = Grafika::createFilter('Grayscale');
        $editor->apply($image, $filter );
        $graypath = substr_replace($path,'-gray.',strrpos($path,'.'),1);
        $editor->save($image,$graypath);
    }
    /*
    * 居中裁剪
    */
    public function resizeFill($path)
    {
        $editor = Grafika::createEditor();
        $editor->open($image, $path);
        $editor->resizeFill($image , 200,200);
        $editor->save($image , $path);
    }

}