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

//代理商后台路由

Route::post("a/login", "agent/user/login");

Route::post("a/logout", "agent/user/logout");

Route::get("a/count", "agent/Resource/index");

Route::get("a/charge", "agent/Resource/chargeManage");

Route::post("a/change_pwd", "agent/user/changePwd");

Route::any("a/m_list", 'agent/Resource/mercList');

Route::any("a/o_list", 'agent/Resource/orderList');

Route::any("a/capital", 'agent/Resource/capitalManage');

Route::any("a/member", 'agent/Resource/memberManage');

Route::any("a/goods", 'agent/Resource/goodsManage');

Route::any("a/m_search", 'agent/Resource/merchantSearch');

Route::any("a/cart", 'agent/Resource/addToCart');

Route::any("a/cat", 'agent/Resource/category');

Route::post("a/opr", 'agent/Resource/cartManage');

Route::get("a/cart_l", 'agent/Resource/cartList');

Route::post("a/num", 'agent/Resource/check_sku');

//商户后台

Route::post("m/login", 'merc/User/login');

Route::post("m/logout", "merc/User/logout");

Route::post("m/change_pwd", "merc/User/changePwd");

Route::any("m/cart", 'merc/Goods/addToCart');

Route::any("m/cat", 'merc/Goods/category');

Route::post("m/opr", 'merc/Goods/cartManage');

Route::get("m/cart_l", 'merc/Goods/cartList');

Route::post("m/num", 'merc/Goods/check_sku');

Route::post("m/cf", 'merc/Goods/confirmReceive');

