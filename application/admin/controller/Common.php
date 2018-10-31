<?php
/**
 * Created by KaiQiang-use by PhpStorm.
 * User: fennu
 * Date: 2018/10/29
 * Time: 10:31
 */

namespace app\admin\controller;


use think\facade\Session;

class Common
{
    public function __construct()
    {
        /** 检查登录 */
        if (Session::has("user")) {
            return true;
        } else {
            return_msg(400, "请登录");
        }
    }

    public function upload_img()
    {
        $file = request()->file('img');
        //移动图片
        $info = $file->validate(['size' => 5 * 1024 * 1024, 'ext' => 'jpg,png,gif,jpeg'])->move('uploads/');

        if ($info) {
            //文件上传成功
            //获取文件路径
            $goods_logo = $info->getSaveName();
            $goods_logo = str_replace('\\', '/', $goods_logo);
            return_msg(200, 'success', '/uploads/' . $goods_logo);
        } else {
            $error = $file->getError();
            return_msg(400, $error);
        }
    }
}