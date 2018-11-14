<?php

namespace app\merc\controller;

use think\App;
use think\Controller;
use think\Db;
use think\Exception;
use think\Request;
use think\facade\Session;

class Goods extends Common
{
    public function __construct(App $app = null)
    {
        parent::__construct($app);
        $this->merc = Session::get("user", "_backend")["id"];
        $this->agent_id = Session::get("user", "_backend")["agent_id"];
    }

    /**
     * 可购买商品 / 加入购物车  POST add
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addToCart(Request $request)
    {
        if ($request->isGet()) {
            $map[] = ["a_id", "eq", $this->agent_id];
            $map[] = ["status", "eq", 1];
            $rows = Db::name("hay")->where($map)->count();
            $pages = page($rows);
            $res["list"] = Db::name("hay")->where($map)->limit($pages["offset"], $pages["limit"])->select();
            $rate = Db::name("setting")->field("recharge")->find();
            foreach ($res["list"] as &$v) {
                $v["fee"] = $v["fee"] * $rate["recharge"];
            }
            $res["pages"] = $pages;
            $res["coin"] = Db::name("merchant")->where("id", $this->merc)->find()["coin"];
            check_data($res["list"], $res);
        } else {
            /** 加入购物车 */
            $gid = $request->post("gid");

//            $ids = explode(",", $gid);
//            check_params("addToCart", $gid);

            $goods = Db::name("goods")->where("id", $gid)->find();
            $data["imgLogo"] = $goods["imgLogo"];
            $data["size"] = $goods["size"];
            $data["buy_price"] = $goods["a_price"];
            $data["sale_price"] = $goods["m_price"];
            $data["fee"] = $goods["fee"];
            $data["m_id"] = $this->merc;
            $data["gid"] = $gid;
            $data["status"] = 0;
            $data["name"] = $goods["name"];
            $res = Db::name("hay")->insertGetId($data);


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
     * 购物车列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cartList()
    {
        $map[] = ["m_id", "eq", $this->merc];
        $map[] = ["status", "eq", 0];
        $rows = Db::name("hay")->where($map)->count();
        $pages = page($rows);
        $res["list"] = Db::name("hay")->where($map)->limit($pages["offset"], $pages["limit"])->select();
        $rate = Db::name("setting")->field("recharge")->find();
        foreach ($res["list"] as &$v) {
            $v["fee"] = $v["fee"] * $rate["recharge"];
        }
        $res["pages"] = $pages;
        check_data($res["list"], $res);
    }

    /**
     * 购买记录
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @param $request
     */
    public function cartRecord(Request $request)
    {
        if ($request->isGet()) {
            $ky = $request->get("ky");
            $status = $request->get("status");
            $time = $request->get("time");

            if ($ky) {
                $k_f = "LIKE";
                $ky = $ky . "%";
            } else {
                $k_f = "NOT LIKE";
                $ky = "-2";
            }
            if ($status) {
                $s_f = "eq";
            } else {
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
     * 商品分类
     * //mark
     */
    public function category()
    {
        $arr = [
            ["cat" => "aaa", "id" => "1"],
            ["cat" => "bbb", "id" => "2"],
            ["cat" => "ccc", "id" => "3"],
        ];
        check_data($arr);
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
            $map[] = ["m_id", "eq", $this->merc];
            $map[] = ["status", "eq", 0];

            if ($opr == "del") {
                $res = Db::name("hay")->where($map)->delete();
                check_opera($res);
            } elseif ($opr == "buy") {
                /** 改变购物车状态 */
                $dd = $request->post();
                check_params('make_order', $dd);
                $rr = Db::name("merchant")->where("id", $this->merc)->find();
                if (!$rr["coin"] || $rr["coin"] < $dd["amount"]) {
                    return_msg(400, "您当前余额不足，请及时充值");
                }

                $sku = explode(',', $dd["num"]);
                $ids = explode(',', $dd["id"]);
                foreach ($ids as $i) {
                    foreach ($sku as $v) {
                        $data = ["num" => $v, "status" => 1];
                        $res = Db::name("hay")->where("id", $i)->update($data);
                    }
                }
                check_opera($res, 0);
                $goods = [
                    "status" => 4,
                    "ctime" => time(),
                    "pay_time" => time(),
                    "order_money" => $dd["t_amount"],
                    "received_coin" => $dd["amount"],
                    "m_id" => $this->merc,
                    "order_no" => generate_order_no($this->merc),
                    "goods" => $dd["gid"],
                    "ads" => $rr["ads"],
                    "c_person" => $rr["contact"],
                    "c_phone" => $rr["phone"],
                    "agent_name" => $rr["agent_name"],
                ];
                $o_res = Db::name("order")->insertGetId($goods);
                check_opera($o_res, 0);
                /** 生成成功 扣除兑换币 */
                $reduce_coin = Db::name("merchant")->where("id", $this->merc)->setDec("coin", $dd["amount"]);

                check_opera($reduce_coin);
            }
        }
    }

    /**
     * 确认收货
     * @param Request $request
     */
    public function confirmReceive(Request $request)
    {
        if ($request->post()) {
            $id = $request->post("id");

            $res = Db::name("order")->where("id", $id)->setField("status", 2);
            check_opera($res);
        }
    }
}
