<?php
namespace app\common\validate;

use think\Validate;

/**
 * Class AdminValidate
 * 参数验证器
 * @package app\common\validate
 */
class AdminValidate extends Validate{
    protected $rule=[
        'phone'  => 'require|length:11',
        'password'   => 'number|length:6',
        "name" => "require|max:20",
        "agent_name" => "require|max:20",
        "c_person" => "require|max:20",
        "c_phone" => "require|max:20",
        "bank_name" => "require",
        "bank_branch" => "require",
        "bank_ads" => "require",
        "account" => "require",
        "account_name" => "require",
        "agent_level" => "require",
        "agent_area" => "require",
        "agent_money" => "require",
        "contract_picture" => "require",
        //改密码
        "ini_pwd" => "require",
        //添加商品
        "imgLogo" => "require",
        "a_price" => "require",
        "m_price" => "require",
        "fee" => "require",
        "SKU" => "require",
        "goods_type" => "require",
        "category" => "require",
        //添加子商品
        "gid" => "require",
        //生成订单
        "t_amount" => "require",
        "num" => "require",
        "amount" => "require",
        "ads" => "require",


    ];

    protected $message = [
        "phone.require" => "电话号码必填",
        "phone.length" => "电话号码必须为11位",
        "password.require" => "密码必填",
        "password.length" => "密码必须为6位",
        "name.require" => "名称必填",
        //新增代理商
        "agent_name.require"  => "代理商名称必填",
        "c_person.require" => "联系人名称必填",
        "c_phone.require" => "联系人电话必填",
        "bank_name.require" => "银行名称必填",
        "bank_branch.require" => "开户行支行必填",
        "bank_ads.require" => "开户行地址必填",
        "account.require" => "银行卡号必填",
        "account_name.require" => "开户人名称必填",
        "agent_level.require" => "代理等级必填",
        "agent_area.require" => "代理区域必填",
        "agent_money.require" => "代理费用必填",
        "contract_picture.require" => "合同图片必填",
        //改密码
        "ini_pwd.require" => "旧密码必填",
        //添加商品
        "SKU.require" => "库存必填",
        "imgLogo.require" => "主图必传",
        "a_price.require" => "代理商价格必填",
        "m_price.require" => "商户价格必填",
        "goods_type.require" => "商品类型必填",
        "status.require" => "商品状态必填",
        "category.require" => "类目必填",
        "category_id.require" => "类目ID必填",
        //添加子商品
        "gid.require"  => "商品ID必填",
        //新增订单
        "t_amount.require" => "订单金额必填",
        "num.require" => "数量必填",
        "amount.require" => "实收金额必填",
        "goods.require" => "商品ID必填",
        "ads.require" => "地址必填",


    ];

    //命名规则 控制器_函数名称
    protected $scene=[
        //改密码
        "change_pwd" => ["phone", "password", "ini_pwd"],
        //登录
        "login"     => ["phone", "password"],
        "agent_login"   => ["phone", "password"],
        //找回密码
        "find_pwd"  => ["phone", "code", 'time', 'password', ],
        //新增代理商
        'add'=>["agent_name", "c_person", "c_phone", "phone", "name", "password", "bank_name", "bank_branch", "bank_ads", "account", "account_name", "agent_level", "agent_area", "agent_money", "contract_picture"],
        "addGoods" => ["name", "a_price", "m_price", "fee", "SKU", "goods_type", "category","imgLogo"],
        "addAliasGoods" => ["name", "a_price", "m_price", "fee", "SKU", "goods_type", "category","imgLogo", "gid"],
        //新增商户 mark
        "add_merc"=>["name", "c_contact", "ads"],
        "make_order"=>["num", "amount", "t_amount", "gid",'id'],
        //add to cart
        "addToCart" => ["gid"],
    ];

}