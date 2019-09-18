<?php

namespace app\admin\model;

use think\Model;

/**
 * 管理员分组模型
 * Class AuthGroup
 * @package app\admin\model
 * @author ^2_3^
 */
class AuthGroup extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function getNameAttr($value, $data)
    {
        return __($value);
    }

}
