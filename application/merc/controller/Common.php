<?php

namespace app\merc\controller;

use think\Controller;
use think\facade\Session;
use think\Request;

class Common extends Controller
{
    protected $merc;
    protected $token;
    protected $agent_id;

    protected function initialize()
    {

        parent::initialize(); // TODO: Change the autogenerated stub
        /** 检查登录 */
        if (Session::has("user", "_backend")) {
            $this->merc = Session::get("user", "_backend")["id"];
            $this->token = md5("JuYouLi_" . md5($this->merc . Session::get("user", "_backend")["name"]));
//            if ($this->token !== \request()->param("token")) {
//                return_msg(400, "token不正确！");
//            }
            unset(request()->param()["token"]);
            return true;
        } else {
            return_msg(400, "请登录");
        }
    }
}
