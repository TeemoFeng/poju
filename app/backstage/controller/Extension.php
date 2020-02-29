<?php
/**
 * Created by PhpStorm.
 * User: Uroaming
 * Date: 2019/11/11
 * Time: 11:38
 */

namespace app\backstage\controller;
use app\common\controller\Base;
use app\backstage\model\Category;
class Extension extends Base
{

    public function items()
    {

        $list = Category::order('id', 'desc')->paginate();
        $this->assign(['items'=>$list]);
        return $this->fetch();

    }

    //生成二维码
    public function createQrcode()
    {

        $id = $this->request->param("id");

        $qrcode = new Qrcode();
        $res = $qrcode->create_qrcode($id, 2);
        if ($res['code'] == 1) {
            //将小程序路径存储到会议表中
            $res = Category::update(['qrcode_path' => $res['path']], ['id' => $id]);
            if ($res === false) {
                return json(['code' => 0, 'msg' => '保存二维码图片失败']);
            }
            return json(['code' => 1, 'msg' => '生成成功']);
        } else {
            return json(['code' => 0, 'msg' => '生成失败']);
        }



    }
}