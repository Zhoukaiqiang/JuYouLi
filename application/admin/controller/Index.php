<?php
/**
 * Created by KaiQiang-use by PhpStorm.
 * User: fennu
 * Date: 2018/10/30
 * Time: 14:59
 */

namespace app\admin\controller;


use app\admin\model\Agent;
use app\admin\model\Capital;
use app\admin\model\Coin;
use app\admin\model\Member;
use app\admin\model\Merchant;
use think\Db;
use think\Request;

class Index extends Common
{

    /**
     * 统计首页数据
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getData(Request $request)
    {
        if ($request->isGet()) {
            /** 昨日交易数据 */

            $Coin = new Coin();
            $total = $Coin->whereTime("pay_time", "yesterday")->field("sum(received_money) received_money, sum(recharge_coin) coin")->find();

            $field = ['coin', "name"];

            $Agent = new Agent();
            $agent = $Agent->field($field)->order("coin", "DESC")->limit(0, 5)->select();
            $agent_num = $Agent->count("id");

            $Merc = new Merchant();
            $merc = $Merc->field($field)->order("coin", "DESC")->limit(0, 5)->select();
            $merc_num = $Merc->count("id");

            $Member = new Member();
            $member = $Member->field($field)->order("coin", "DESC")->limit(0, 5)->select();
            $mem_num = $Member->count("id");


            /** 组合数据 */
            $data["received_money"] = $total["received_money"];
            $data["recharge_coin"] = $total["coin"];
            $data["agent_num"] = $agent_num;
            $data["merc_num"] = $merc_num;
            $data["mem_num"] = $mem_num;
            $data["list"]["agent"] = $agent;
            $data["list"]["merc"] = $merc;
            $data["list"]["member"] = $member;

            check_data($data);
        }

    }

    /**
     * 交易 昨日 ---7天 ---- 30天
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function trade(Request $request)
    {
        if ($request->isGet()) {
            $time = $request->param("time");

            if (!empty($time)) {
                $time_flag = 'between';
                $time = [$time];
            } else {
                $time_flag = ">=";
                $time = strtotime("today");
            }
            $Coin = new Coin();
            $field = ["sum(received_money) received_money , sum(recharge_coin) coin"];
            $res = $Coin->whereTime("pay_time", $time_flag, $time)->field($field)->find();

            $recover = Db::name("capital")->whereTime("exec_time", $time_flag, $time)->field("sum(money) money, sum(coin) coin")->find();
            $Agent = Agent::count("id");
            $Merchant = Merchant::count("id");
            $Member = Member::whereTime("ctime", "yes")->count("id");
            /** 组合数据 */

            $data["total"] = $res["received_money"];
            $data["coin"] = $res["coin"];
            $data["given_money"] = $recover["money"];
            $data["given_coin"] = $recover["coin"];
            $data["agent"] = $Agent;
            $data["merchant"] = $Merchant;
            $data["member"] = $Member;

            check_data($data);
        }

    }

    /**
     * 图表
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function diagram(Request $request)
    {
        if ($request->isGet()) {

            if ($request->get("_coin")) {
                $field = ["sum(recharge_coin) received_money, count(id) num"];
                $this->get_data($field);
            } elseif ($request->get("_money")) {
                $field = ["sum(received_money) received_money, count(id) num"];
                $this->get_data($field);
            } elseif ($request->get("_recover")) {
                $field = ["sum(coin) received_money, count(id) num"];
                $i = -7;
                /** 默认展示7天数据 */
                while ($i < 0) {
                    $morning = strtotime(date('Y-m-d 00:00:00', strtotime($i . ' days')));
                    $night = strtotime(date('Y-m-d 23:59:59', strtotime($i . ' days')));
                    $date = [$morning, $night];

                    $data['chartData'][] = Capital::whereTime('exec_time', "between", $date)
                        ->field($field)
                        ->find();
                    $i++;
                }
                check_data($data["chartData"], '', 0);

                foreach ($data["chartData"] as $k => $v) {

                    $filted_data[] = [
                        "amount" => $v["received_money"],
                        "count" => $v["num"],
                        "pay_time" => date('Y-m-d', strtotime($i + $k . ' days')),
                    ];
                }

                check_data($filted_data);
            }
        }
    }

    /**
     * 获取数据
     * @param $param
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function get_data($param)
    {
        $i = -7;
        /** 默认展示7天数据 */
        while ($i < 0) {
            $morning = strtotime(date('Y-m-d 00:00:00', strtotime($i . ' days')));
            $night = strtotime(date('Y-m-d 23:59:59', strtotime($i . ' days')));
            $date = [$morning, $night];

            $data['chartData'][] = Coin::whereTime('pay_time', "between", $date)
                ->field($param)
                ->find();
            $i++;
        }
        check_data($data["chartData"], '', 0);

        foreach ($data["chartData"] as $k => $v) {

            $filted_data[] = [
                "amount" => $v["received_money"],
                "count" => $v["num"],
                "pay_time" => date('Y-m-d', strtotime($i + $k . ' days')),
            ];
        }
        check_data($filted_data);
    }

    /**
     * 充值管理
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function chargeManage(Request $request)
    {
        if ($request->isGet()) {
            $rows = Db::name("coin")->count("id");
            $pages = page($rows);
            $res["list"] = Db::name("coin")->limit($pages["offset"], $pages["limit"])->select();
            $res["page"] = $pages;
            check_data($res["list"], $res);
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
            }else {
                $ky = "-2";
                $ky_f = "neq";
            }
            if ($time) {
                $night = strtotime(date("Y-m-d 23:59:59", $time));
                $time = [(int)$time, $night];
                $time_f = "between";
            }else {
                $time = -2;
                $time_f = ">";
            }
            $where[] = ["username", $ky_f, $ky];
            $where[] = ["exec_time", $time_f, $time];

            if (!empty($request->get("record"))) {
                $rows = Capital::where("status <> 0")->where($where)->count("id");
                $pages = page($rows);
                $res["list"] = Capital::where("status <> 0")->where($where)->limit($pages["offset"], $pages["limit"])->select();
                $res["page"] = $pages;

            } else {
                $rows = Capital::where($where)->count("id");
                $pages = page($rows);
                $res["list"] = Capital::limit($pages["offset"], $pages["limit"])->where($where)->select();
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
            $coin = Capital::get($id);
            if((int)$status == 2) {
                $user = Agent::get($id);
                $user->coin	= ['inc', $coin->coin];
                $add = $user->save();
                check_opera($add, 1);
            }else {
                $res = Capital::update([
                    "status" => $status
                ], ["id" => $id]);
                check_opera($res);

            }


        }
    }

    /**
     * 设置兑换比例
     * @param [float] $scale
     */
    public function setScale()
    {
        $float = \request()->param("scale");
        $res = Db::name("setting")->where("id = 1")->setField("scale", (float)$float);
        check_opera($res);
    }

    /**
     * 设置充值比例
     * @param [float] $rate
     */
    public function setRecharge()
    {
        $float = \request()->param("rate");
        $res = Db::name("setting")->where("id = 1")->setField("recharge", (float)$float);
        check_opera($res);
    }
}