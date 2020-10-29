<?php
/**
 * Created by PhpStorm.
 * User: Dr.Loveumore
 * Date: 2018-06-20
 * Time: 17:49
 */

namespace app\backstage\controller;
use app\common\controller\Base;
use app\backstage\model\Topic as TP;
use app\backstage\model\Category;
class Topic extends Base
{
    public function index()
    {
        $this->request->filter('');
        $topic = new TP();
        if ($this->request->isPost())
        {
            $data = $this->request->post();
            $model = $topic->where('id','=',$data['id'])->find();
            if (!empty($model))
            {
                $model->delete();
            }
            $data['id']=0;
            return $topic->baseSave($data);
        }else{
            $id = $this->request->param('id');
            $sid = $this->request->param('sid', 1);
            $category = new Category();
            $where['sid'] = $sid;
            if (!empty($id)){
                $where['tid'] = $id;
                $data = $topic->where($where)->find();
                if (empty($data)){
                    $data['tid'] = $id;
                }
                $this->assign('model',$data);
            }

            $nav = $category->order('id desc')->select();
            $this->assign("list",$nav);
            $this->assign("key",$sid);
            return $this->fetch();
        }
    }
//    public function text()
//    {
//        $this->request->filter('');
//        $topic = new TP();
//        if ($this->request->isPost())
//        {
//            $data = $this->request->post();
//            $model = $topic->where('tid','=',$data['tid'])->find();
//            if (!empty($model))
//            {
//                $model->delete();
//            }
//            $data['id']=0;
//            return $topic->baseSave($data);
//        }else{
//            $id = $this->request->param('id');
//            if (!empty($id)){
//                $data = $topic->where('tid','=',$id)->find();
//                if (empty($data)){
//                    $data['tid']=$id;
//                }
//                $this->assign('model',$data);
//            }
//            return $this->fetch();
//        }
//    }

    public function text()
    {
        $this->request->filter('');
        $topic = new TP();
        if ($this->request->isPost())
        {
            $data = $this->request->post();
            $model = $topic->where('id','=',$data['id'])->find();
            if (!empty($model))
            {
                $model->delete();
            }
            $data['id']=0;
            return $topic->baseSave($data);
        }else{
            $id = $this->request->param('id');
            $sid = $this->request->param('sid', 1);
            $category = new Category();
            $where['sid'] = $sid;
            if (!empty($id)){
                $where['tid'] = $id;
                $data = $topic->where($where)->find();
                if (empty($data)){
                    $data['tid'] = $id;
                }
                $this->assign('model',$data);
            }

            $nav = $category->order('id desc')->select();
            $this->assign("list",$nav);
            $this->assign("key",$sid);
            return $this->fetch();
        }
    }
    public function fixed()
    {
        $this->request->filter('');
        $topic = new TP();
        if ($this->request->isPost())
        {
            $data = $this->request->post();
            if ($data['id']=='0'){
                $res = $topic->isUpdate(false)->allowField(true)->save($data);
            }else{
                $res = $topic->isUpdate(true)->allowField(true)->save($data);
            }
            return $res===false?json(['code'=>2,'msg'=>'保存失败！']):json(['code'=>1,'msg'=>'保存成功！']);
        }else{
            $tid = $this->request->get('tid');
            $tpl = $this->request->get('tpl');
            $model = $topic->where('tid','=',$tid)->find();
            if (!empty($model)){
                $this->assign('model',$model);
            }else{
                $this->assign('tid',$tid);
            }
            return $this->fetch($tpl?:'');
        }
    }
}