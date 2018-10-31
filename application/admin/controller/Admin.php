<?php

namespace app\admin\controller;

use app\admin\model\Agent;
use app\admin\model\Member;
use app\admin\model\Merchant;
use app\common\validate\AdminValidate;
use think\console\command\make\Validate;
use think\Controller;
use think\Db;
use think\Exception;
use think\Loader;
use think\Request;
use think\validate\ValidateRule;

class Admin extends Common
{

    /**
     * 获取单个代理商 / 代理商列表
     * @param Request $request
     * @throws \think\Exception\DbException
     */
    public function getAgent(Request $request)
    {
        $Agent = new Agent();
        $id = $request->param("id");
        if ($request->isGet()) {

            if (!empty($id)) {
                $res = $Agent::get($id);
                $res->hidden(["password"]);
                check_data($res);
            }
            $rows = $Agent::count("id");
            $pages = page($rows);
            $res["list"] = $Agent->limit($pages["offset"], $pages["limit"])->select();

            $res["list"]->hidden(["password"]);

            $res["pages"] = $pages;
            check_data($res["list"], $res);
        } else {
            /** 修改代理商 */
            $data = $request->only(["status", "coin"]);

            if (count($data)) {
                $res = $Agent->isUpdate(true)->save($data, ["id" => $id]);
                if ($res) {
                    return_msg(200, "success");
                } else {
                    return_msg(400, "fail");
                }
            } else {
                return_msg(400, "fail");
            }
        }

    }

    /**
     * 获取单个商户/商户列表
     * @param Request $request
     * @throws \think\Exception\DbException
     */
    public function getMerc(Request $request)
    {
        $Merc = new Merchant();
        if ($request->isGet()) {
            $id = $request->param("id");

            if (!empty($id)) {
                $res = $Merc::get($id);
                $res->hidden(["password"]);
                check_data($res);
            }
            $rows = $Merc::count("id");
            $pages = page($rows);
            $res["list"] = $Merc->limit($pages["offset"], $pages["limit"])->select();

            $res["list"]->hidden(["password"]);

            $res["pages"] = $pages;
            check_data($res["list"], $res);
        } else {
            /**  商户修改 */
            $id = $request->only("id");
            $data = $request->only(["status", "coin"]);

            if (count($data)) {
                $res = $Merc->isUpdate(true)->save($data, ["id" => $id]);
                if ($res) {
                    return_msg(200, "success");
                } else {
                    return_msg(400, "fail");
                }
            } else {
                return_msg(400, "fail");
            }

        }
    }

    /**
     * 获取会员列表 / 单个会员详情
     * @param $request [id] 传入ID获取某个会员详情
     * @return [json]
     * @throws Exception
     */
    public function getMember(Request $request)
    {
        if ($request->isGet()) {
            $ky = $request->get("ky");
            $id = $request->get("id");

            if ($ky) {
                $ky_f = "LIKE";
                $ky = $ky . "%";
            }else {
                $ky_f = "neq";
                $ky = "-2";
            }

            $where[] = ["name|phone", $ky_f, $ky];
            $Member = new Member();
            if (!empty($id)) {
                $res = $Member::get($id);
                $res->hidden(["password"]);
                check_data($res);
            }
            $rows = $Member::where($where)->count("id");
            $pages = page($rows);
            $res["list"] = $Member->where($where)->limit($pages["offset"], $pages["limit"])->select();

            $res["list"]->hidden(["password"]);
            $res["pages"] = $pages;
            check_data($res["list"], $res);
        } else {
            /** 会员修改 */
            $id = $request->param("id");
            $param = $request->only("coin");
            $res = Member::get($id)->isUpdate(true)->save($param, ["id" => $id]);

            if ($res) {
                return_msg(200, "success");
            } else {
                return_msg(400, "fail");
            }

        }
    }


    /**
     * 新增代理商
     * @method POST
     * @param  $contract_time [file]    合同有效期
     * @param  $contract_picture [file] 合同图片
     * @return \think\Response
     */
    public function addAgent(Request $request)
    {
        if (request()->isPost()) {
            $data = request()->post();

            //发送密码到代理商
//            $check_send = send_msg_to_phone($data['phone'], $data['password']);
//            if (!$check_send) {
//                return_msg(400, "短信发送失败");
//            }
            //验证
            $validate = new  AdminValidate;
            check_exists("agent", "name", $data['phone'], 0);
            if ($validate->scene('add')->check($data)) {

                $data['password'] = encrypt_password($data["password"], $data["phone"]);
                //保存到数据表
                $id = $request->post("id") ? $request->post("id") : null;
                if ($id) {
                    $info = Agent::save($data, ["id" => $id]);
                }else {
                    $info = Agent::save($data, true);
                }

                if ($info) {
                    return_msg('200', '保存成功', $info);
                } else {
                    return_msg('400', '保存失败');
                }
            } else {
                return_msg(400, $validate->getError());
            }
        }
    }



}
