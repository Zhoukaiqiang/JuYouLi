<?php

namespace app\goods\controller;

use app\admin\controller\Common;
use app\common\validate\AdminValidate;
use Endroid\QrCode\QrCode;
use think\Db;
use think\Request;

class Goods
{
    /**
     * 添加商品
     *
     * @return \think\Response
     */
    public function addGoods(Request $request)
    {
        if ($request->isPost()) {
            $param = $request->post();
            $param["ctime"] = time();
            $param["status"] = 1;
            /** @var [bool] 检查参数 $param */
            $validate = new AdminValidate();
            if ($validate->scene("addGoods")->check($param)) {
                $res = Db::name("goods")->insertGetId($param);
            } else {
                return_msg(400, $validate->getError());
            }
            check_opera($res);
        }
    }

    /**
     * 添加商品规格
     * @param Request $request
     */
    public function addAliasGoods(Request $request)
    {
        if ($request->isPost()) {
            $param = $request->post();
            $param["ctime"] = time();
            $param["status"] = 1;
            /** @var [bool] 检查参数 $param */
            $validate = new AdminValidate();
            if ($validate->scene("addAliasGoods")->check($param)) {
                $res = Db::name("goods")->insertGetId($param);
            } else {
                return_msg(400, $validate->getError());
            }
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
            /** 发货操作 */
            if ($send) {
                $where = [
                    ["status", "eq", 1]
                ];
            } else {
                $where = [
                    ["status", "neq", 0]
                ];
            }
            $where[] = ["agent_name|c_phone|c_person", $ky_f, $ky];
            $where[] = ["ctime", $time_f, $time];
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
     * 订单详情
     * @param Request $request
     * @param [string] $gs 商品id + , + 商品id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function orderDetail(Request $request)
    {
        if ($request->isGet()) {
            $str = $request->get("gs");
            $arr = explode(',', $str);
            $where = [
                "gid" => 0,
                "id" => $arr,
            ];
            $main_id = Db::name("goods")->where($where)->field("id")->select();
            $rows = Db::name("goods")->where($where)->count("id");
            $pages = page($rows);
            $res["list"] = Db::name("goods")->where($where)->limit($pages["offset"], $pages["limit"])->select();
            foreach ($main_id as &$v) {
                $v = $v["id"];
            }
            $arr = array_diff($arr, $main_id);  //删除指定元素
            foreach ($res["list"] as &$v) {
                foreach ($arr as $i) {
                    $where2 = [
                        'gid' => $v["id"],
                        "id" => $i,
                    ];
                    $alias['alias_' . $i] = Db::name("goods")->where($where2)->find();
                    if(!$alias["alias_".$i]) {
                        unset($alias["alias_".$i]);
                    }
                }
                if (empty($alias)) {
                    continue;
                }
                $v["alias"] = $alias;
                $alias = null;
            }
            $res["pages"] = $pages;
            check_data($res["list"], $res);
        }
    }


    /**
     * 获取商品和关联商品详情 图片, 价格,数量
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodsDetail(Request $request)
    {
        if ($request->isGet()) {
            $gid = $request->get("gid");
            $res = Db::name("goods")->where("id", $gid)->find();
            $alias = Db::name("goods")->where("gid", $gid)->select();
            $res["alias"] = $alias;
            check_data($res);
        }
    }

    /**
     * 生成二维码
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function makeQrCode(Request $request)
    {
        if ($request->isPost()) {
            $gid = $request->post("gid");
            $url = "http://" . $_SERVER['SERVER_NAME'];
            $qrCode = new QrCode("$url/goods/index.php?gid=$gid");
            $imgName = md5($qrCode->getText());
            $path = "uploads/" . $imgName . ".png";
            $qrCode->writeFile($path);
            return_msg(200, "Success", $path);
        }
    }

    /**
     * 商品列表
     * @param  \think\Request $request
     * @return \think\Response
     */
    public function goodsList(Request $request)
    {
        if ($request->isGet()) {
            $gType = $request->get("goods_type");  // 1--兑换币  2-- 优惠券

            if (!empty($gType)) {
                $where[] = ["goods_type", "eq", $gType];

            } else {
                $where[] = ["goods_type", "eq", 1];
            }
            $where[] = ["gid", "eq", 0];

            $rows = Db::name("goods")->where($where)->count("id");
            $pages = page($rows);

            $all_goods = Db::name("goods")->where($where)->limit($pages['offset'], $pages["limit"])->select();
            check_data($all_goods, '', 0);

            foreach ($all_goods as &$v) {
                $v["alias"] = Db::name("goods")->where("gid", $v['id'])->select();
            }

            $res["list"] = $all_goods;
            $res["page"] = $pages;
            check_data($res["list"], $res);

        } else {
            $param = $request->post();
            $id = $request->post("id");
            $unsale = $request->post("unsale");
            if (isset($unsale)) {
                $res = Db::name("goods")->where($id)->update("status", 0);
                check_opera($res);
            }
            /** 修改数据 */
            $res = Db::name("goods")->where($id)->update($param);
            check_opera($res);
        }
    }

    /**
     * 商品搜索
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function searchGoods(Request $request)
    {
        if ($request->isPost()) {

            $ky = $request->post("ky");
            $status = $request->post("status");
            $sku = $request->post("sku");
            $request->post("status");
            $query['goods_type'] = $request->post("goods_type");  // 1--兑换币  2-- 优惠券
            if ($ky) {
                $ky_f = "LIKE";
                $ky = $request->post("ky") . '%';
            } else {
                $ky_f = "NOT LIKE";
                $ky = -2;
            }
            if ($status) {
                $status_f = "eq";
            } else {
                $status_f = "neq";
                $status = -2;
            }
            if ($sku) {
                $sku_f = "<";
                $sku = (int)$sku;
            } else {
                $sku_f = "<>";
                $sku = -2;
            }
            $where[] = ["name", $ky_f, $ky];
            $where[] = ["status", $status_f, $status];
            $where[] = ["SKU", $sku_f, $sku];

            if (isset($query['goods_type'])) {
                $where[] = ["goods_type", "eq", $query['goods_type']];
            } else {
                $where[] = ["goods_type", "eq", 1];
            }

            $where[] = ["gid", "eq", 0];

            $rows = Db::name("goods")->where($where)->count("id");
            $pages = page($rows);
            $all_goods = Db::name("goods")->where($where)->limit($pages['offset'], $pages["limit"])->select();
            check_data($all_goods, '', 0);
            foreach ($all_goods as &$v) {
                $v["alias"] = Db::name("goods")->where("gid", $v['id'])->select();
            }

            $res["list"] = $all_goods;
            $res["page"] = $pages;

            check_data($res["list"], $res);
        }
    }


}
