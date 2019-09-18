<?php

namespace app\common\model;

use think\Model;

/**
 * Class UserGroup
 * @package app\common\model
 * @author ^2_3^
 */
class UserGroup extends Model
{

    // 表名
    protected $name = 'user_group';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
    ];

}
