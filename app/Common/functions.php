<?php
/**
 * Created by KaiQiang-use by PhpStorm.
 * User: fennu
 * Date: 2018/10/27
 * Time: 17:24
 * Usage: 全局公共函数
 */

/**
 * 返回数据
 * @param $code  [int]  状态码
 * @param $msg   [string]  返回消息
 * @param array $data [返回数据]
 */
function showMsg($code , $msg, $data = []) {
    $result = array(
        'status' => $code,
        'message' =>$msg,
        'data' =>$data
    );
    exit(json_encode($result));

}

/**
 * 检查是否有数据并返回
 * @param array $data  要检查的数据
 * @param null $return_data  要返回的数据
 * @param bool $exist    是否返回
 * @return bool / msg  检查信息
 */
function checkData(Array $data, $return_data = null, $exist = true) {
    if ($exist) {
        if (count($data)) {
            if (empty($return_data)) {
                $return_data = $data;
            }
            showMsg(200, "success",$return_data);
        }else {
            showMsg(400, "no data");
        }
    }else {
        if (count($data)) {
            return true;
        }else {
            showMsg(400, "no data");
        }
    }
}