<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /** 后台登录人员 */
    public function show()
    {
        Admin::getUser();
    }

    public function agent_list()
    {
        $res = new Agent();
        dd($res);
        checkData($res);
    }
}
