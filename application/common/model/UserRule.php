<?php

namespace app\common\model;

use think\Model;

/**
 * 会员规则模型
 * Class UserRule
 * @package app\common\model
 * @author ^2_3^
 */
class UserRule extends Model
{

    // 表名 会员规则表
    protected $name = 'user_rule';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
    ];

}
