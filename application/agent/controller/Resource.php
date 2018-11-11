<?php

namespace app\agent\controller;

use app\agent\model\Merchant;
use think\Db;
use think\Exception;
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
                ->limit(0, 5)
                ->select();

            foreach ($res["m_list"] as $v) {
                $m_id[] = $v['id'];
            }

            $res['mm_list'] = Db::name("member")->where("m_id", 'IN', $m_id)
                ->field("name ,coin,id")
                ->order("coin", "DESC")
                ->limit(0, 5)
                ->select();
            check_data($res["m_list"], $res);
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
            if ($request->param("add")) {
                check_params("add_merc", $data);
                $res = Db::name("merchant")->insertGetId($data);
                check_opera($res);
            } else {
                $res = Db::name("merchant")->where("id", $data["id"])->find();
                check_data($res);
            }


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
                $res = Db::name("order")->where("id = $id")->setField("status", 0);
                check_opera($res);
            } else {
                /** @var [int]改为发送中 $res */
                $res = Db::name("order")->where("id = $id")->setField("status", 3);
                check_opera($res);
            }

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
                $ky_f = "NOT LIKE";
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
            $where[] = ["a_id", "eq", $this->agent_id];
            if (!empty($request->get("record"))) {
                $where[] = ["exec_time", $time_f, $time];
                $rows = Db::name("capital")->where("status <> 0")->where($where)->count("id");
                $pages = page($rows);
                $res["list"] = Db::name("capital")->where("status <> 0")->where($where)->limit($pages["offset"], $pages["limit"])->select();
                $res["page"] = $pages;
            } else {
                $where[] = ["ctime", $time_f, $time];
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

            $coin = Db::name("capital")->where("id = $id")->field("coin")->find();

            if ((int)$status == 2) {
                Db::name("capital")->where($id)->setField("status", 2);
                /** 驳回  返回兑换币 */
                $add = Db::name("merchant")->where("id", $this->agent_id)->setInc("coin", $coin['coin']);
                check_opera($add);
            } else {
                /** 结算 $res */
                $res = Db::name("capital")->where("id = $id")->setField('status', $status);
                check_opera($res);
            }


        }
    }


    /**
     * 商品列表
     * @param  \think\Request $request
     * @return \think\Response
     * @throws Exception
     */
    public function goodsManage(Request $request)
    {
        if ($request->isGet()) {
            $ky = $request->get("ky");
            $sku = $request->get("sku");
            //mark
            $category = $request->get("cat");
            if ($ky) {
                $k_f = "LIKE";
                $ky = $ky . "%";
            } else {
                $k_f = "NOT LIKE";
                $ky = "-2";
            }
            if ($category) {
                $cat_f = "LIKE";
            } else {
                $cat_f = "NOT LIKE";
                $category = "-2";
            }
            if ($sku) {
                $sku_f = "<";
            } else {
                $sku_f = ">";
                $sku = -2;
            }
            $where[] = ["gid", "eq", 0];
            $where[] = ["name", $k_f, $ky];
            $where[] = ["sku", $sku_f, $sku];
            $where[] = ["category", $cat_f, $category];

            $rows = Db::name("goods")->where($where)->count("id");
            $pages = page($rows);
            $all_goods = Db::name("goods")->where($where)->limit($pages['offset'], $pages["limit"])->select();
            check_data($all_goods, '', 0);

            foreach ($all_goods as &$v) {
                $v["alias"] = Db::name("goods")->where("gid", $v['id'])->select();
                foreach ($v["alias"] as &$c) {
                    $c["l_sku"] = Db::name("hay")->where(["gid" => $v["id"], "a_id" => $this->agent_id])->field("sku")->find();
                }
            }
            $res["list"] = $all_goods;
            $res["page"] = $pages;
            check_data($res["list"], $res);
        }
    }

    /**
     * 商品分类
     * //mark
     */
    public function category()
    {
        $arr = [
            ["cat" => "aaa", "id" => "1"],
            ["cat" => "bbb", "id" => "2"],
            ["cat" => "ccc", "id" => "1"],
        ];
        check_data($arr);
    }

    /**
     * 购物车列表 / 加入购物车  POST add
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addToCart(Request $request)
    {
        if ($request->isGet()) {
            $rows = Db::name("hay")->where("a_id", $this->agent_id)->count();
            $pages = page($rows);
            $res["list"] = Db::name("hay")->where("a_id", $this->agent_id)->limit($pages["offset"], $pages["limit"])->select();
            $rate = Db::name("setting")->field("recharge")->find();
            foreach ($res["list"] as &$v) {
                $v["fee"] = $v["fee"] * $rate["recharge"];
            }
            $res["pages"] = $pages;
            check_data($res["list"], $res);
        } else {
            /** 加入购物车 */
            $gid = $request->post("gid");

            $ids = explode(",", $gid);
            foreach ($ids as $i) {
                $goods = Db::name("goods")->where("id = $i")->find();
                $data["imgLogo"] = $goods["imgLogo"];
                $data["size"] = $goods["size"];
                $data["buy_price"] = $goods["a_price"];
                $data["sale_price"] = $goods["m_price"];
                $data["fee"] = $goods["fee"];
                $data["a_id"] = $this->agent_id;
                $data["gid"] = $i;
                $data["status"] = 0;
                $data["name"] = $goods["name"];
                $res = Db::name("hay")->insertGetId($data);
            }

            check_opera($res);
        }
    }

    /**
     * 检查是否大于库存量
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function check_sku(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->post("gid");
            $res = Db::name("goods")->where("id = $id")->field("SKU")->find();
            $max = $request->post("num");
            if ($max > $res["SKU"]) {
                return_msg(400, "大于当前库存");
            }
        }
    }

    /**
     * 购买列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @param $request
     */
    public function cartList(Request $request)
    {
        if ($request->isGet()) {
            $ky = $request->get("ky");
            $status = $request->get("status");
            $time = $request->get("time");

            if ($ky) {
                $k_f = "LIKE";
                $ky = $ky . "%";
            }else {
                $k_f = "NOT LIKE";
                $ky = "-2";
            }
            if ($status) {
                $s_f = "eq";
            }else {
                $s_f = "neq";
                $status = -2;
            }
            if ($time) {
                $night = strtotime(date("Y-m-d 23:59:59", $time));
                $time = [(int)$time, $night];
                $time_f = "between";
            } else {
                $time_f = ">";
                $time = -2;
            }
            $map[] = ["ctime", $time_f, $time];
            $map[] = ["agent_name|c_person|c_phone", $k_f, $ky];
            $map[] = ["status", $s_f, $status];
            $map[] = ["a_id", "eq", $this->agent_id];
            $map[] = ["status", "eq", 1];
            $rows = Db::name("order")->where($map)->count();
            $pages = page($rows);
            $res["list"] = Db::name("order")->where($map)->limit($pages["offset"], $pages["limit"])->select();
            $res["pages"] = $pages;
            check_data($res["list"], $res);
        }
    }

    /**
     * 购物车操作
     * @param Request $request
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public function cartManage(Request $request)
    {
        if ($request->isPost()) {
            $opr = $request->post("opr");  // buy / del
            $hay_id = $request->post("id");  // 购物车ID
            $map[] = ["id", "eq", $hay_id];
            $map[] = ["a_id", "eq", $this->agent_id];
            if ($opr == "del") {
                $res = Db::name("hay")->where($map)->delete();
                check_opera($res);
            } elseif ($opr == "buy") {

                /** 改变购物车状态 */
                $dd = $request->post();
                $rr = Db::name("agent")->where("id", $this->agent_id)->find();
                if (!$rr["coin"] || $rr["coin"] < $dd["amount"] ) {
                    return_msg(400, "您当前余额不足，请及时充值");
                }
                check_params('make_order',$dd);

                $sku = explode(',', $dd["num"]);
                $ids = explode(',', $dd["id"]);
                foreach ($ids as $i) {
                    foreach ($sku as $v) {
                        $data = ["num" => $v, "status" => 1];
                        $res = Db::name("hay")->where("id = $i")->update($data);
                    }
                }
                check_opera($res, 0);

                $goods = [
                    "status" => 4,
                    "ctime" => time(),
                    "pay_time" => time(),
                    "order_money" => $dd["t_amount"],
                    "received_coin" => $dd["amount"],
                    "a_id" => $this->agent_id,
                    "order_no" => generate_order_no($this->agent_id),
                    "goods" => $dd["gid"],
                    "ads" => $rr["ads"],
                    "c_person" => $rr["c_person"],
                    "c_phone" => $rr["c_phone"],
                    "agent_name" => $rr["agent_name"],
                ];
                $o_res = Db::name("order")->insertGetId($goods);
                check_opera($o_res, 0);
                /** 生成成功 扣除兑换币 */
                $reduce_coin = Db::name("merchant")->where("id", $this->agent_id)->setDec("coin", $dd["amount"]);

                check_opera($reduce_coin);
            }
        }
    }
    /**
     * 会员管理搜索
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function merchantSearch(Request $request)
    {
        if ($request->isPost()) {
            $ky = $request->post("ky");

            if ($ky) {
                $k_f = "LIKE";
                $ky = $ky . "%";
            } else {
                $k_f = "NOT LIKE";
                $ky = $ky . "-2";
            }
            $where[] = ["name", $k_f, $ky];
            $where[] = ["agent_id", "eq", $this->agent_id];
            $where[] = ["group_id", "eq", 0];
            $res = Db::name("merchant")->where($where)->field("name,id")->select();
            check_data($res);
        } else {
            $id = $request->get("id");
            $db_res = Db::name("merchant")->where("group_id", "IN", $id)->field("name,id")->select();
            check_data($db_res);
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
            $where = [
                ["role", 'eq', 1],
                ["status", 'eq', 1],
                ["user_id", "IN", $mem_id]
            ];
            if ($time) {
                $t_f = "between";
                $night = strtotime(date("Y-m-d 23:59:59", $time));
                $time = [$time, $night];
            } else {
                $t_f = ">=";
                $time = -2;
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

    /**
     * 会员管理
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws Exception
     */
    public function MemberManage(Request $request)
    {
        if ($request->isGet()) {
            $rows = Db::name("group")->where("a_id", $this->agent_id)->count("id");
            $pages = page($rows);
            $res['list'] = Db::name("group")->where("a_id", $this->agent_id)
                ->limit($pages["offset"], $pages["limit"])
                ->select();

            check_data($res["list"], '', 0);
            foreach ($res["list"] as &$v) {
                $v["names"] = Db::name("merchant")->where("id", "IN", $v["m_ids"])->field("name , id")->select();
            }
            $res["pages"] = $pages;
            check_data($res);

        } else {
            $id = $request->post("id");
            $opr = $request->post("opr");
            $mid = $request->post("mid");
            $where = [
                "id" => $id,
                "a_id" => $this->agent_id,
            ];
            if ($opr == "del_g") {
                $res = Db::name("group")->where($where)->delete();
                Db::name("merchant")->where("group_id = $id")->setField("group_id", 0);
                check_opera($res);
            } elseif ($opr == "del_m" && $mid) {
                $res = Db::name("group")->where($where)->field("m_ids")->find();
                $arr = explode(',', $res['m_ids']);
                $mid = [(int)$mid];
                $ar = array_diff($arr, $mid);

                Db::name("merchant")->where("id = $mid[0]")->setField("group_id", 0);
                $re = Db::name("group")->where($where)->update(["m_ids" => implode(",", $ar)]);
                check_opera($re);
            } elseif ($opr == "add_g") {
                $data["m_ids"] = $request->post("m_ids");
                $data["a_id"] = $this->agent_id;
                $res = Db::name("group")->insertGetId($data);
                check_opera($res);
            } elseif ($opr == "add_m") {
                $res = Db::name("group")->where($where)->field("m_ids")->find();
                $arr = explode(',', $res['m_ids']);
                if (in_array($mid, $arr)) {
                    return_msg(400, "该成员已存在！");
                } else {
                    Db::name("merchant")->where("id = $mid")->setField("group_id", $id);
                    array_push($arr, $mid);
                    $data["m_ids"] = implode(',', $arr);
                    $update = Db::name("group")->where($where)->update($data);
                    check_opera($update);
                }
            }
        }

    }

}
