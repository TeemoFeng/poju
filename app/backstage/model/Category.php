<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-05-21
 * Time: 17:24
 */

namespace app\backstage\model;
use traits\model\SoftDelete;
class Category extends BaseModel
{
    use SoftDelete;
    protected $updateTime = false;
    public function getChildByPid($pid){
        $child = $this->field('id,pid,root_path,name')->where('root_path', 'like', "%-$pid-%")->order('sort','asc')->select();
        return $child ?: $this->where('id','=',$pid)->select();
    }
    public function getChildIdlist($pid){
        $child = $this->where('root_path', 'like', "%-$pid-%")->order('sort','asc')->column('id');
        return $child?implode(',',$child):null;
    }
    public function getRootId($id){
        $path = $this->where('id','=',$id)->value('root_path');
        return explode('-',$path)[1];
    }
    public function getRootModel($id){
        $path = $this->where('id','=',$id)->value('root_path');
        $rid = explode('-',$path)[1];
        return $this->find($rid);
    }
    public function addNode($data){
        $this->startTrans();
        try {
            $this->allowField(true)->data($data,true)->save();
            $id = $this->id;
            if (empty($data['pid'])) {
                $this->where('id',$id)->update(['root_path' => '0-' . $id]);
            } else {
                $parentPath = $this->where('id', intval($data['pid']))->value('root_path');
                $this->where('id',$id)->update(['root_path' => "$parentPath-$id"]);
            }
            $this->commit();
            $res =['msg'=>'保存成功！','code'=>1];
        } catch (\Exception $e) {
            $this->rollback();
            $res =['msg'=>'保存出错了！','code'=>2];
        }
        return json($res);
    }
    public function editNode($data){
        $res = ['msg'=>'保存成功！','code'=>1];
        $id          = intval($data['id']);
        $parentId    = intval($data['pid']);
        $oldCategory = $this->where('id', $id)->find();
        if (empty($parentId)) {
            $newPath = '0-' . $id;
        } else {
            $parentPath = $this->where('id', $parentId)->value('root_path');
            if ($parentPath === false) {
                $newPath = false;
            } else {
                $newPath = "$parentPath-$id";
            }
        }
        if (empty($oldCategory) || empty($newPath)) {
            $res = ['msg'=>'保存出错了！','code'=>2];
        } else {
            $data['root_path'] = $newPath;
            $this->isUpdate(true)->allowField(true)->save($data, ['id' => $id]);
            $children = $this->field('id,root_path')->where('root_path', 'like', "%-$id-%")->select();
            if (!empty($children)) {
                foreach ($children as $child) {
                    $childPath = str_replace($oldCategory['root_path'] . '-', $newPath . '-', $child['root_path']);
                    $child->root_path = $childPath;
                    $child->save();
                }
            }
        }
        return $res;
    }
    public function GetTypeIsListChildByPid($pid)
    {
        $child = $this->field('id,pid,root_path,name')->where('root_path', 'like', "%-$pid-%")->where('type_id','=',2)->order('sort','asc')->select();
        return $child ?: $this->where('id','=',$pid)->select();
    }

    public function getLastOne()
    {
        return  $this->where('state','=',1)->order('sort','asc')->order('id','desc')->limit(1)->find();
    }

}