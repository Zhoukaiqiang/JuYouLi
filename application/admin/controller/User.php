<?php
/**
 * Created by KaiQiang-use by PhpStorm.
 * User: fennu
 * Date: 2018/10/29
 * Time: 10:33
 */

namespace app\admin\controller;


use think\Controller;
use think\Db;
use think\Exception;
use think\facade\Session;
use think\Request;

class User extends Controller
{

    public function encrypt_password()
    {
        $req = \request()->param();
        $pwd = $req["password"];
        $phone = $req["phone"];
        return md5('$YouShop' . md5($pwd) . $phone);
    }

    /**
     * 用户登录
     * @param [strin]   phone 用户名（电话）
     * @param [stirng]  password  用户密码
     * @return [json] 返回信息
     * @throws Exception
     */
    public function login()
    {
        $data = request()->post();
        check_params("login", $data);

        check_exists('admin', 'phone', $data['phone'], 1);

        $db_res = Db::name("admin")->field('id,name,phone,password')
            ->where('phone', $data['phone'])->find();

        if ($db_res['password'] !== encrypt_password($data['password'], $data["phone"])) {
            return_msg(400, '用户密码不正确！');
        } else {
            unset($db_res['password']); //密码不返回
            //存储session信息
            Session::set("user", $db_res);

            return_msg(200, '登录成功！', $db_res);
        }
    }

    /**
     * 用户登出
     */
    public function logout()
    {
        Session::clear();
        if (Session::has("user")) {
            return_msg(400, "fail");
        } else {
            return_msg(200, "success");
        }
    }

    /**
     * 用户改密码
     * @param [int] phone 用户手机号
     * @param [string] ini_pwd 老密码
     * @param [string]  password 新密码
     * @return [json] 返回消息
     * @throws Exception
     */
    public function changePwd(Request $request)
    {
        /* 接受参数 */
        $query = $request->post();

        check_params("change_pwd", $query);
        /* 检测用户名并取出数据库中的密码 */
        check_exists('admin', 'phone', $query["phone"], 1);
        $where['phone'] = $query['phone'];

        /* 判断原始密码是否正确 */
        $db_ini_pwd = Db::name("admin")->where($where)->value("password");

        if ($db_ini_pwd !== encrypt_password($query['ini_pwd'], $query["phone"])) {
            return_msg(400, '旧密码不正确!');
        }

        $res = Db::name('admin')->where($where)->setField('password', encrypt_password($query['password'], $query['phone']));

        /* 把新的密码存入数据库 */
        if ($res) {
            return_msg(200, '密码修改成功！');
        } else {
            return_msg(400, '密码修改失败！');
        }
    }
}