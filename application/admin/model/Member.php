<?php

namespace app\admin\model;

use think\Model;

class Member extends Model
{

    protected $autoWriteTimestamp = true;
    protected $createTime = "ctime";
    protected $updateTime = false;

}
