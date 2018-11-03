<?php

namespace app\agent\controller;

use app\agent\model\Merchant;
use think\Db;
use think\Request;

class Resource extends Common
{

    /**
     * 首页统计
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index(Request $request)
    {
        if ($request->isGet()) {
            $res['ta'] = Db::name("merchant")->where("agent_id", $this->agent_id)->count("id");
            $res["ta_new"] = Db::name("merchant")->where("agent_id", $this->agent_id)->whereTime("ctime", "yesterday")->count("id");
            $res["ta_c"] = Db::name("merchant")->where("agent_id", $this->agent_id)->sum("coin");

            $res['m_list'] = Db::name("merchant")->where("agent_id", $this->agent_id)
                ->field("name ,coin,id")
                ->order("coin", "DESC")
                ->limit(0,5)
                ->select();

            foreach ($res["m_list"] as $v) {
                $m_id[] = $v['id'];
            }

            $res['mm_list'] = Db::name("member")->where("m_id", 'IN', $m_id)
                ->field("name ,coin,id")
                ->order("coin", "DESC")
                ->limit(0,5)
                ->select();
            check_data($res["m_list"] , $res);
        }

    }

    /**
     * 商户管理
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function mercList(Request $request)
    {
        if ($request->isGet()) {
            $status = $request->get("status");
            $ky = $request->get("ky");
            if ($status) {
                $st_f = "eq";
            } else {
                $st_f = "neq";
                $status = "-1";
            }
            if ($ky) {
                $ky_f = "LIKE";
                $ky = $ky . "%";
            } else {
                $ky_f = "NOT LIKE";
                $ky = "-1";
            }
            $where[] = ["name|agent_name|contact|phone", $ky_f, $ky];
            $where[] = ["status", $st_f, $status];
            $where[] = ["agent_id", 'eq', $this->agent_id,];

            $rows = Db::name("merchant")->where($where)->count("id");
            $pages = page($rows);
            $res["list"] = Db::name("merchant")->where($where)->limit($pages["offset"], $pages["limit"])->select();
            $res["page"] = $pages;
            check_data($res["list"], $res);
        } else {
            $data = $request->post();
            check_params("add_merc", $data);
            $res = Db::name("merchant")->insertGetId($data);
            check_opera($res);
        }
    }


    /**
     * 订单列表
     * @param [int /string] $send 已发货列表 / 申请列表
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function orderList(Request $request)
    {
        if ($request->isGet()) {
            $send = $request->get("send");
            $ky = $request->get("ky");
            $time = $request->get("time");

            if ($ky) {
                $ky_f = "LIKE";
                $ky = $ky . "%";
            } else {
                $ky_f = "NOT LIKE";
                $ky = '-2';
            }
            if ($time) {
                $night = strtotime(date("Y-m-d 23:59:59", $time));
                $time = [(int)$time, $night];
                $time_f = "between";
            } else {
                $time_f = ">";
                $time = -2;
            }
            /** 已发货列表 */
            if ($send) {
                $where[] = ["status", "eq", 1];
            } else {
                $where[] = ["status", "neq", 0];
            }

            $where[] = ["agent_name|c_phone|c_person", $ky_f, $ky];
            $where[] = ["ctime", $time_f, $time];
            $where[] = ["a_id", 'eq', $this->agent_id];
            $rows = Db::name("order")->where($where)->count("id");
            $pages = page($rows);
            $res["list"] = Db::name("order")->where($where)->limit($pages['offset'], $pages["limit"])->select();
            $res["page"] = $pages;
            check_data($res["list"], $res);
        } else {
            $id = $request->post("id");
            $status = $request->post("status");

            if ((int)$status == 0) {
                $res = Db::name("order")->where($id)->setField("status", 0);
            } else {
                /** @var [int]改为发送中 $res */
                $res = Db::name("order")->where($id)->setField("status", 3);
            }
            check_opera($res);
        }

    }


    /**
     * 兑换管理  申请 / 记录
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function capitalManage(Request $request)
    {
        if ($request->isGet()) {
            $ky = $request->get("ky");
            $time = $request->get("time");
            if ($ky) {
                $ky = $ky . "%";
                $ky_f = "LIKE";
            } else {
                $ky = "-2";
                $ky_f = "neq";
            }
            if ($time) {
                $night = strtotime(date("Y-m-d 23:59:59", $time));
                $time = [(int)$time, $night];
                $time_f = "between";
            } else {
                $time = -2;
                $time_f = ">";
            }
            $where[] = ["username", $ky_f, $ky];
            $where[] = ["exec_time", $time_f, $time];
            $where[] = ["a_id", "eq", $this->agent_id];
            if (!empty($request->get("record"))) {
                $rows = Db::name("capital")->where("status <> 0")->where($where)->count("id");
                $pages = page($rows);
                $res["list"] = Db::name("capital")->where("status <> 0")->where($where)->limit($pages["offset"], $pages["limit"])->select();
                $res["page"] = $pages;

            } else {
                $rows = Db::name("capital")->where($where)->count("id");
                $pages = page($rows);
                $res["list"] = Db::name("capital")->limit($pages["offset"], $pages["limit"])->where($where)->select();
                $res["page"] = $pages;
            }

            $scale = Db::name("setting")->field("scale");
            $res["scale"] = $scale;
            check_data($res["list"], $res);
        } else {
            /** 操作 */
            $id = $request->post("id");
            $status = $request->post("status");

            /** 返回兑换币 */
            $coin = Db::name("capital")->where($id)->find();
            if ((int)$status == 2) {
                $user = Merchant::get($id);
                $user->coin = ['inc', $coin->coin];
                $add = $user->save();
                check_opera($add, 1);
            } else {
                $res = Db::name("order")->where($id)->update(["status" => $status]);
                check_opera($res);

            }


        }
    }


    /**
     * 首页充值管理
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function chargeManage(Request $request)
    {
        if ($request->isGet()) {
            $time = $request->get("time");
            $merc_id = Db::name("merchant")->where("agent_id", $this->agent_id)->column("id");
            $mem_id = Db::name("member")->where("m_id", "IN", $merc_id)->column("id");
            $where =[
                ["role" ,'eq', 1],
                ["status" ,'eq', 1],
                ["user_id" ,"IN", $mem_id]
            ];
            if ($time) {
                $t_f = "between";
                $night = strtotime(date("Y-m-d 23:59:59", $time));
                $time = [$time, $night];
            }else {
                $t_f = ">=";
                $time  = -2;
            }
            $where[] = ["ctime", $t_f, $time];
            $rows = Db::name("coin")->where($where)->count("id");
            $pages = page($rows);
            $res["list"] = Db::name("coin")
                ->where($where)
                ->limit($pages["offset"], $pages["limit"])->select();
            $res["page"] = $pages;
            check_data($res["list"], $res);
        }
    }

}
