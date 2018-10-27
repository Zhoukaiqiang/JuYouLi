<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    /** @var string 指定表名 */
    protected $table = "admin";
    protected $primaryKey = "id";

    public static function getUser() {

    }
}
