<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-06-21
 * Time: 10:27
 */

namespace app\backstage\controller;
use app\common\controller\Base;

class FileList extends Base
{
    public function index()
    {
        $id = $this->request->param('id');
        if (empty($id)||$id==1){
            $title = '系统图片';
            $path = '/static/images/';
        }else{
            $title = '素材图片';
            $path = '/upload/image/';
        }
        $this->assign('titel',$title);
        $this->assign('path',$path);
      return $this->fetch();
    }
    public function imageList()
    {

        $allowFiles = [".png", ".jpg", ".jpeg", ".gif", ".bmp"];
        $listSize = 40;
        $allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);
        /* 获取参数 */
        $size = $this->request->param('size')?: $listSize;
        $start = $this->request->param('start')?: 0;
        $end = $start + $size;
        $path = $this->request->param('path');
        /* 获取文件列表 */
        $path = $_SERVER['DOCUMENT_ROOT'] . (substr($path, 0, 1) == "/" ? "":"/") . $path;
        $files = $this->getfiles($path, $allowFiles);
        if (!count($files)) {
            return json(array(
                "state" => "no match file",
                "list" => array(),
                "start" => $start,
                "size" => $size,
                "total" => count($files)
            ));
        }
        /* 获取指定范围的列表 */
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
            $list[] = $files[$i];
        }
        /* 返回数据 */
        $result = json(array(
            "state" => "SUCCESS",
            "list" => $list,
            "start" => $start,
            "size" => $size,
            "total" => count($files)
        ));
        return $result;
    }
    function getfiles($path, $allowFiles, &$files = array())
    {
        if (!is_dir($path)) return null;
        if(substr($path, strlen($path) - 1) != '/') $path .= '/';
        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $path2 = $path . $file;
                if (is_dir($path2)) {
                    $this->getfiles($path2, $allowFiles, $files);
                } else {
                    if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
                        $files[] = array(
                            'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
                            'mtime'=> filemtime($path2)
                        );
                    }
                }
            }
        }
        return $files;
    }
    public function fileDelete()
    {
        $path = $this->request->post('path');

        return  unlink('.'.$path)?1:0;
    }
}