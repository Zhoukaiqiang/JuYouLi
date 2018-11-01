<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

//Route::get('think', function () {
//    return 'hello,ThinkPHP5!';
//});
//
//Route::get('hello/:name', 'index/hello');

Route::post("login", "admin/user/login");

Route::post("logout", "admin/user/logout");

Route::post("change_pwd", "admin/user/changePwd");

Route::post("upload", "admin/common/upload_img");

Route::any("get_agent", "admin/admin/getAgent");

Route::any("get_merc", "admin/admin/getMerc");

Route::any("get_member", "admin/admin/getMember");

Route::post("add_agent", "admin/admin/addAgent");

Route::any("add_merc", "admin/admin/addMerc");

Route::get("count", "admin/index/getData");

Route::get("trade", "admin/index/trade");

Route::get("diagram", "admin/index/diagram");

Route::get("charge", "admin/index/chargeManage");

Route::any("capital", "admin/index/capitalManage");

Route::any("st_scale", "admin/index/setScale");

Route::any("st_recharge", "admin/index/setRecharge");
//商品 -- 订单

Route::any("order", "goods/goods/orderList");

Route::get("order_d", "goods/goods/orderDetail");

Route::any("goods", "goods/goods/goodsList");


Route::post("add_goods", "goods/goods/addGoods");

Route::post("aa_goods", "goods/goods/addAliasGoods");

Route::post("search_goods", "goods/goods/searchGoods");

return [

];
