<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
if (!function_exists('encrypt_password')) {
    /**
     * 密码加密
     * @param [sting] $password [加密前的密码]
     * @param [string] $val 用户手机号
     * @return [string] [加密后的密码]
     *
     */
    function encrypt_password($password, $phone = '')
    {

        return md5('$YouShop' . md5($password) . $phone);
    }
}
/**
 * 生成验证码
 * @param [int] [验证码的位数]
 * @return [int]   [生成的验证码]
 *
 *
 */
if (!function_exists('make_code')) {
    function make_code($num)
    {
        $max = pow(10, $num) - 1;
        $min = pow(10, $num - 1);
        return rand($min, $max);
    }
}

/**
 * api 数据返回
 * @param [int] $code [结果码 200：正常/4**数据问题/5**服务器问题]]
 * @param [string] $msg [接口码要返回的提示信息]
 * @param [array] $data [接口要返回的数据]
 *
 */
if (!function_exists('return_msg')) {
    function return_msg($code, $msg = '', $data = [])
    {
        /* 组合数据 */
        $return_data['code'] = $code;
        $return_data['msg'] = $msg;
        $return_data['data'] = $data;
        /* ---------返回信息并终止脚本---------- */

        echo json_encode($return_data);
        die;
    }
}

/**
 * 验证请求是否超时
 * @param [int] $code [结果码 200：正常 /400：错误 /500：服务器问题]]
 * @param [string] $msg [接口码要返回的提示信息]
 * @param [array] $data [接口要返回的数据]
 *
 */
if (!function_exists('check_time')) {
    function check_time()
    {
        $time = time();
        session('check_time', $time);
        if ((time() - session('check_time')) < 0.0001) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * 分页
 * $page 当前页
 * $rows 总行数
 * $limit 每页显示的记录数
 */
if (!function_exists('page')) {
    function page($rows, $limit = 5)
    {
        $page = request()->param('page') ? request()->param('page') : 1;
        //获取总页数
        $pageCount = ceil($rows / $limit);
        //偏移量
        $offset = ($page - 1) * $limit;
        //上一页
        $pagePrev = $page - 1;
        if ($pagePrev <= 1) {
            $pagePrev = 1;
        }
        //下一页
        $pageNext = $page + 1;
        if ($pageNext >= $pageCount) {
            $pageNext = $pageCount;
        }
        $data['pageCount'] = $pageCount;
        $data['offset'] = $offset;
        $data['pagePrev'] = $pagePrev;
        $data['pageNext'] = $pageNext;
        $data['limit'] = $limit;
        $data['rows'] = $rows;

        return $data;
    }
}

/**
 * 参数验证
 * 验证规则写在Common模块 1--AdminValidate 2--MerchantValidate 3--AgentValidate
 */
if (!function_exists("check_params")) {
    function check_params($scene, $param)
    {
        $validate = new \app\common\validate\AdminValidate();

        if (!$validate->scene($scene)->check($param)) {

            return_msg(400, $validate->getError());
        }
    }
}

if (!function_exists("generate_order_no")) {
    /**
     * 生成订单号 规则:  /[日期+用户ID+3位随机码]/
     * @param $uid [int] 用户ID
     * @return [string] 订单号码
     */
    function generate_order_no($uid = null)
    {

        if (empty($uid)) {
            $uid = rand(100, 999);
        }
        $order_num = (string)date("YmdHis") + (string)$uid + rand(100, 999);
        return $order_num;

    }
}

/**
 * 发送curl
 * @param [string] $url 请求地址
 * @param [bool]   $post POST|GET
 * @param [array]  $params 请求参数
 * @param [bool]   $https 是否使用HTTPS
 */
if (!function_exists('curl_request')) {
    //使用curl函数库发送请求
    function curl_request($url, $post = false, $params = [], $https = false)
    {
        $params = json_encode($params);
        //①使用curl_init初始化请求会话
        $ch = curl_init();
        //②使用curl_setopt设置请求一些选项

        //测试地址 http://sandbox.starpos.com.cn/emercapp
        //正式地址 https://gateway.starpos.com.cn/emercapp
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($post) {
            //设置请求方式、请求参数
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array("application/json;charset=GBK", "Content-length:" . strlen($params)));
        }
        if ($https) {
            //https协议，禁止curl从服务器端验证本地证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        //③使用curl_exec执行，发送请求
        //设置 让curl_exec 直接返回接口的结果数据
        $res = curl_exec($ch);
        //④使用curl_close关闭请求会话
        $res = iconv('GBK', 'UTF-8', $res);
        curl_close($ch);
        return $res;
    }
}

if (!function_exists('sign_ature')) {
    function signature($flag, Array $arr, $key = null)
    {
        $key = $key ? $key : KEY;

//        $arr=array_change_key_case($arr);

        ksort($arr);
        if ($flag == 0000) {
            $data = [
                //客户主扫
                //查询
                "qryNo",
                'payChannel', 'authCode', 'total_amount', 'amount', 'trmNo', 'tradeNo', 'txnTime', 'signType', 'opSys', 'characterSet', 'serviceId', 'version', 'incom_type', 'stl_typ', 'stl_sign', 'stl_oac', 'bnk_acnm', 'wc_lbnk_no', 'bus_lic_no', 'bse_lice_nm', 'crp_nm', 'mercAdds', 'bus_exp_dt', 'crp_id_no', 'crp_exp_dt', 'stoe_nm', 'stoe_cnt_nm', 'stoe_cnt_tel', 'mcc_cd', 'stoe_area_cod', 'stoe_adds', 'trm_rec', 'mailbox', 'alipay_flg', 'yhkpay_flg', 'mercId', 'orgNo', 'imgTyp', 'imgNm', 'log_no', 'stoe_id', 'lbnk_nm'];
            $str = '';

            foreach ($arr as $k => $v) {
                if (in_array($k, $data)) {

                    $str .= $v;
                }
            }

        } elseif ($flag == 1111) {
            $data = ['result', 'logNo', 'tradeNo', 'sysTime', 'message', 'returnCode', 'check_flag', 'msg_cd', 'msg_dat', 'mercId', 'log_no', 'stoe_id', 'mobile', 'sign_stats', 'deliv_stats'];
            $str = '';
            foreach ($arr as $key1 => $val) {
                if (in_array($key1, $data)) {
                    $str .= $val;
                }
            }
        }
        return md5($str . $key);
    }
}

/**
 * 发送[验证码]类型短信到手机
 * @param $phone
 * @param $msg
 */
function send_msg_to_phone($phone, $msg)
{
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, 'https://api.mysubmail.com/message/xsend.json');
    //curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //post数据
    curl_setopt($curl, CURLOPT_POST, 1);
    //配置submail
    $data = [
        'appid' => '27075', //应用id
        'to' => $phone,     //要接受短信的电话
        'project' => 'Jaayb', //模板标识
        'vars' => "{'code': '" . $msg . "'}",
        'signature' => '5ac305ef38fb126d2a0ec5304040ab7d', //应用签名
    ];

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($curl);
    curl_close($curl);
    $res = json_decode($res);
    if ($res->status !== 'success') {
        return false;
    } else {
        return true;
    }

}

/**
 * 检测用户是否存在于数据库
 * @param string $db [数据库全称]
 * @param $phone [要检查的手机号]
 * @param $exist $exist [ 1 / 0 ]
 * @return bool / [msg]  检验结果
 * @author K'
 */
if (!function_exists('check_exists')) {
    function check_exists($db = 'youshop_admin', $field = "phone", $phone, $exist = 0)
    {
        $result = \think\Db::name($db)->where($field, $phone)->find();
        if ($exist == 1) {
            if ($result) {
                return true;
            } else {
                return_msg(400, "账号不存在");
            }
        } elseif ($exist == 0) {
            /** 用户为不存在的情况 */
            if ($result) {
                return_msg(400, '用户已存在！');
            } else {
                return true;
            }
        }

    }
}

/**
 * 生成商品编码
 */
if( !function_exists("goods_no") ) {
    function goods_no($factory = "01", $mcc = "13", $goods_id, $sku) {
        /** 默认变量 */
        $factory = strval($factory); //厂家号
        $mcc = strval($mcc);     //大类编码
        $goods_id = strval($goods_id); // 产品ID
        $sku = strval($sku);     // 库存

        return ($factory . $mcc . $goods_id . $sku);
    }
}
/**
 * 检验是否有数据，并返回结果
 * @param $data [要检查的数据]
 * @param null $return_data [要返回的数据]
 * @param $return 0---不返回数据  1---返回数据
 * @return [json]  返回信息
 * @author K'
 */
function check_data($data, $return_data = null, $return = 1)
{
    if ($return == 1) {
        if (!$data) {
            return_msg(400, "no data");
        } elseif (gettype($data) !== 'array') {
            $data = $data->toArray();
        }
        if (!$return_data) {
            $return_data = $data;
        }
        if (count($data)) {
            return_msg(200, "success", $return_data);
        } else {
            return_msg(400, "no data");
        }
    } else {
        if (!$data) {
            return_msg(400, "no data");
        } else {
            return true;
        }
    }
}

/**
 * 检查操作是否成功
 * @param $res
 */
function check_opera($res, $checkReturn = 1)
{
    if ($checkReturn) {
        if ($res) {
            return_msg(200, "成功");
        } else {
            return_msg(400, "失败");
        }
    } else {
        if ($res) {
            return true;
        } else {
            return_msg(400, "失败");
        }
    }

}

